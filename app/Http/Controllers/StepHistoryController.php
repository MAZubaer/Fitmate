<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StepHistory;

class StepHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stepHistory = StepHistory::where('user_id', $user->id)
            ->orderBy('date', 'asc')
            ->get(['date', 'steps']);
        return response()->json(['history' => $stepHistory]);
    }
}
