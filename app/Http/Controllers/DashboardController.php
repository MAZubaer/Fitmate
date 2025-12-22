<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_workouts' => 0, // Placeholder
            'total_calories' => 0, // Placeholder
            'this_month' => date('F Y'),
            'joined_date' => $user->created_at->format('M d, Y'),

            // ✅ ADD THESE (meal system)
            'meal_streak' => $user->meal_streak,
            'meal_points' => $user->meal_points,
        ];

        return Inertia::render('Dashboard', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
