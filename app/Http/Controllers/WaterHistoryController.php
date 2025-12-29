<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WaterHistory;

class WaterHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $waterHistory = WaterHistory::where('user_id', $user->id)
            ->orderBy('date', 'asc')
            ->get(['date', 'amount']);
        return response()->json(['history' => $waterHistory]);
    }
}
