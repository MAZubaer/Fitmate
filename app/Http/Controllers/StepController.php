<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StepHistory;
use Carbon\Carbon;

class StepController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'steps' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $stepHistory = StepHistory::firstOrNew([
            'user_id' => $user->id,
            'date' => $today,
        ]);
        $stepHistory->steps += $request->steps;
        $stepHistory->save();

        return response()->json(['success' => true, 'steps' => $stepHistory->steps]);
    }

    public function today(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $stepHistory = StepHistory::where('user_id', $user->id)->where('date', $today)->first();
        $steps = $stepHistory ? $stepHistory->steps : 0;
        return response()->json(['steps' => $steps]);
    }
}
