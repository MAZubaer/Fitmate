<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Meal;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Calories burned per day for last 7 days (sum of completed workouts)
        $workoutStartDate = now()->subDays(6)->toDateString();
        $workoutEndDate = now()->toDateString();
        $workouts = \App\Models\Workout::where('user_id', $user->id)
            ->where('completed', true)
            ->whereDate('date', '>=', $workoutStartDate)
            ->whereDate('date', '<=', $workoutEndDate)
            ->selectRaw('DATE(date) as date, SUM(calories) as calories')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $calorieBurnedChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $calorieBurnedChart[] = [
                'date' => $date,
                'calories' => isset($workouts[$date]) ? (int)$workouts[$date]->calories : 0,
            ];
        }
        $user = Auth::user();

        $today = now()->toDateString();
        $weekStart = now()->subDays(6)->toDateString();

        // Calories today
        $todayCalories = Meal::where('user_id', $user->id)
            ->whereDate('meal_date', $today)
            ->sum('calories');

        // Calories this week
        $weeklyCalories = Meal::where('user_id', $user->id)
            ->whereDate('meal_date', '>=', $weekStart)
            ->sum('calories');

        // Daily calorie goal
        $dailyGoal = $user->calorie_goal ?? 2200;

        // Calories per day for last 7 days (today + previous 6 days, always 7 days, fill missing with 0)
        $startDate = now()->subDays(6)->toDateString();
        $endDate = now()->toDateString();
        $meals = Meal::where('user_id', $user->id)
            ->whereDate('meal_date', '>=', $startDate)
            ->whereDate('meal_date', '<=', $endDate)
            ->selectRaw('DATE(meal_date) as date, SUM(calories) as calories')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $calorieChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $calorieChart[] = [
                'date' => $date,
                'calories' => isset($meals[$date]) ? (int)$meals[$date]->calories : 0,
            ];
        }

        // Goal logic (5 goals per week)
        $goalsCompleted = 0;
        foreach ($calorieChart as $day) {
            if ($day['calories'] >= $dailyGoal) {
                $goalsCompleted++;
            }
        }

        $stats = [
            'total_workouts' => 0, // teammate safe
            'total_calories' => $weeklyCalories,
            'today_calories' => $todayCalories,
            'daily_goal' => $dailyGoal,
            'goal_percent' => $dailyGoal > 0 
                ? round(($todayCalories / $dailyGoal) * 100)
                : 0,
            'goals_completed' => $goalsCompleted,
            'this_month' => date('F Y'),
            'joined_date' => $user->created_at->format('M d, Y'),
            'meal_streak' => $user->meal_streak ?? 0,
            'meal_points' => $user->meal_points ?? 0,
        ];

        return Inertia::render('Dashboard', [
            'user' => $user,
            'stats' => $stats,
            'calorieChart' => $calorieChart,
            'calorieBurnedChart' => $calorieBurnedChart,
        ]);
    }
}
