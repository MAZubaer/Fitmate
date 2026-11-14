<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Models\BmiHistory;
use App\Models\Meal;
use App\Models\StepHistory;
use App\Models\WaterHistory;
use App\Models\Workout;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiMealAssistantController extends Controller
{
    /** Earlier exchanges sent along so the coach remembers the conversation. */
    private const HISTORY_EXCHANGES = 10;

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are FitMate, a professional fitness coach.
You give advice on workouts, nutrition, hydration, recovery and health.
You can chat with the user about fitness related topics.
You can use the user's real fitness data to give personalized advice when the user asks for personalized advice.
PROMPT;

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $user = Auth::user();
        $session = $request->input('session_id') ?? (string) Str::uuid();

        $messages = [
            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
            ['role' => 'system', 'content' => $this->fitnessProfile($user->id)],
            ...$this->conversationHistory($user->id, $session),
            ['role' => 'user', 'content' => $request->prompt],
        ];

        try {
            $response = Http::withToken(config('services.groq.key'))
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(45)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'openai/gpt-oss-20b',
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Groq request failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'The AI coach took too long to respond. Please try again.'], 504);
        }

        $reply = $response->json('choices.0.message.content');

        if ($response->failed() || ! is_string($reply) || $reply === '') {
            Log::warning('Groq returned an error', ['status' => $response->status()]);

            return response()->json(['error' => 'AI service failed'], 502);
        }

        AiChat::create([
            'user_id' => $user->id,
            'session_id' => $session,
            'user_message' => $request->prompt,
            'ai_reply' => $reply,
        ]);

        return response()->json([
            'reply' => $reply,
            'session_id' => $session,
        ]);
    }

    /** The most recent exchanges in this session, oldest first, as chat messages. */
    private function conversationHistory(int $userId, string $session): array
    {
        return AiChat::where('user_id', $userId)
            ->where('session_id', $session)
            ->latest('id')
            ->take(self::HISTORY_EXCHANGES)
            ->get(['user_message', 'ai_reply'])
            ->reverse()
            ->flatMap(fn ($chat) => [
                ['role' => 'user', 'content' => (string) $chat->user_message],
                ['role' => 'assistant', 'content' => (string) $chat->ai_reply],
            ])
            ->values()
            ->all();
    }

    private function fitnessProfile(int $userId): string
    {
        $today = now()->toDateString();

        $meals = Meal::where('user_id', $userId)->latest('meal_date')->take(5)->get(['name', 'calories', 'meal_date']);
        $workouts = Workout::where('user_id', $userId)->latest('date')->take(5)->get(['name', 'calories', 'date', 'completed']);
        $steps = StepHistory::where('user_id', $userId)->whereDate('date', $today)->value('steps');
        $water = WaterHistory::where('user_id', $userId)->whereDate('date', $today)->value('amount');
        $bmi = BmiHistory::where('user_id', $userId)->latest('date')->value('bmi');

        $profile = "User fitness profile (today is {$today}):\n";
        $profile .= 'BMI: '.($bmi ?? 'Not updated')."\n\n";

        $profile .= "Recent meals:\n";
        $profile .= $meals->isEmpty()
            ? "- No meals logged\n"
            : $meals->map(fn ($m) => "- {$m->name} ({$m->calories} kcal on ".substr((string) $m->meal_date, 0, 10).')')->implode("\n")."\n";

        $profile .= "\nRecent workouts:\n";
        $profile .= $workouts->isEmpty()
            ? "- No workouts logged\n"
            : $workouts->map(fn ($w) => "- {$w->name}, {$w->calories} kcal, ".substr((string) $w->date, 0, 10).($w->completed ? ' (completed)' : ' (planned)'))->implode("\n")."\n";

        $profile .= "\nSteps today: ".($steps ?? 0)."\n";
        $profile .= 'Water today: '.($water ?? 0)." ml\n";

        return $profile;
    }
}
