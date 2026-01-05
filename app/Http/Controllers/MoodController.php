<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyMood;
use Carbon\Carbon;

class MoodController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'mood' => 'required|in:HAPPY,NEUTRAL,STRESSED'
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $mood = DailyMood::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $today
            ],
            [
                'mood' => $request->mood
            ]
        );

        return response()->json([
            'success' => true,
            'mood' => $mood->mood
        ]);
    }

    public function today()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $mood = DailyMood::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'mood' => $mood ? $mood->mood : null
        ]);
    }
}
