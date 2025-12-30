<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\AiChat;

class AiMealAssistantController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
        ]);

        // Always ensure we have a valid session ID
        $session = $request->input('session_id');
        if (!$session) {
            $session = (string) Str::uuid();
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.groq.key'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI meal assistant that gives healthy meal suggestions.'
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

        AiChat::create([
            'user_id'      => Auth::id(),
            'session_id'   => $session,
            'user_message'=> $request->prompt,
            'ai_reply'    => $reply,
        ]);

        return response()->json([
            'reply' => $reply,
            'session_id' => $session,
        ]);
    }
}
