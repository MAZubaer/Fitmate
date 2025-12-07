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

        // Get user statistics
        $stats = [
            'total_workouts' => 0, // Placeholder - add your logic
            'total_calories' => 0, // Placeholder - add your logic
            'this_month' => date('F Y'),
            'joined_date' => $user->created_at->format('M d, Y'),
        ];

        return Inertia::render('Dashboard', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
