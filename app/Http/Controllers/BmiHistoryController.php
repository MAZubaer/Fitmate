<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BmiHistory;

class BmiHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $bmiHistory = BmiHistory::where('user_id', $user->id)
            ->orderBy('date', 'asc')
            ->get(['date', 'bmi']);
        return response()->json(['history' => $bmiHistory]);
    }
}
