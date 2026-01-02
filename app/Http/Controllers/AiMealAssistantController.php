<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\AiChat;
use App\Models\Meal;
use App\Models\Workout;
use App\Models\StepHistory;
use App\Models\WaterHistory;
use App\Models\BmiHistory;

class AiMealAssistantController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
        ]);

        // Ensure session
        $session = $request->input('session_id') ?? (string) Str::uuid();

        $user = Auth::user();

        // Fetch real user data
        $meals = Meal::where('user_id', $user->id)
            ->latest('meal_date')
            ->take(5)
            ->get(['name','calories','meal_date']);

        $workouts = Workout::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(['name','calories','date']);

        $steps = StepHistory::where('user_id', $user->id)
            ->latest()
            ->first();

        $water = WaterHistory::where('user_id', $user->id)
            ->latest()
            ->first();

        $bmi = BmiHistory::where('user_id', $user->id)
            ->latest()
            ->first();

        // Build user fitness profile
        $profile = "User fitness profile:\n";

        $profile .= "BMI: " . ($bmi ? $bmi->bmi : "Not updated") . "\n\n";

        $profile .= "Recent meals:\n";
        if ($meals->count()) {
            foreach ($meals as $m) {
                $profile .= "- {$m->name} ({$m->calories} kcal on {$m->meal_date})\n";
            }
        } else {
            $profile .= "- No meals logged\n";
        }

        $profile .= "\nRecent workouts:\n";
        if ($workouts->count()) {
            foreach ($workouts as $w) {
                $profile .= "- {$w->name} burned {$w->calories} kcal on {$w->date}\n";
            }
        } else {
            $profile .= "- No workouts logged\n";
        }

        $profile .= "\nSteps today: " . ($steps ? $steps->steps : "No data") . "\n";
        $profile .= "Water today: " . ($water ? $water->amount . " ml" : "No data") . "\n";

        // Send to Groq AI
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.groq.key'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are FitMate, a professional fitness coach. 
You give advice on workouts, nutrition, hydration, recovery and health. 
You can chat with the user about fitness related topics.
You can use the user’s real fitness data to give personalized advice when the user asks for personalized advice.'
                ],
                [
                    'role' => 'system',
                    'content' => $profile
                ],
                [
                    'role' => 'user',
                    'content' => $request->prompt
                ],
            ],
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'AI service failed'
            ], 500);
        }

        $reply = $response->json('choices.0.message.content');

        // Save chat
        AiChat::create([
            'user_id'       => $user->id,
            'session_id'    => $session,
            'user_message' => $request->prompt,
            'ai_reply'     => $reply,
        ]);

        return response()->json([
            'reply' => $reply,
            'session_id' => $session,
        ]);
    }
}
