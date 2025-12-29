<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BmiHistory;
use Carbon\Carbon;

class BmiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'height' => 'required|numeric|min:0.5|max:3',
            'weight' => 'required|numeric|min:10|max:500',
            'bmi'    => 'required|numeric|min:5|max:100',
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Only allow one BMI entry per user per day
        $existing = BmiHistory::where('user_id', $user->id)->where('date', $today)->first();
        if ($existing) {
            return response()->json(['error' => 'You can only update BMI once in a day'], 422);
        }

        $bmi = BmiHistory::create([
            'user_id' => $user->id,
            'date'    => $today,
            'height'  => $request->height,
            'weight'  => $request->weight,
            'bmi'     => $request->bmi,
        ]);

        return response()->json(['success' => true, 'bmi' => $bmi]);
    }
}
