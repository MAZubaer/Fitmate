<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WaterHistory;
use Carbon\Carbon;

class WaterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $waterHistory = WaterHistory::firstOrNew([
            'user_id' => $user->id,
            'date' => $today,
        ]);
        $waterHistory->amount += $request->amount;
        $waterHistory->save();

        return response()->json(['success' => true, 'amount' => $waterHistory->amount]);
    }

    public function today(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $waterHistory = WaterHistory::where('user_id', $user->id)->where('date', $today)->first();
        $amount = $waterHistory ? $waterHistory->amount : 0;
        return response()->json(['amount' => $amount]);
    }
}
