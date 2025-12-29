<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Workout;
use Carbon\Carbon;

class RecentActivityController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $next24 = $now->copy()->addDay();

        $workouts = Workout::where('user_id', $user->id)
            ->whereBetween('date', [$now->toDateString(), $next24->toDateString()])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        return response()->json(['workouts' => $workouts]);
    }
}
