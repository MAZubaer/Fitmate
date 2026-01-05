<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Meal;
use App\Models\Workout;

class DashboardController extends Controller
{
    /**
     * Dashboard page
     */
    public function index()
    {
        $user = Auth::user();

        /* -------------------------------
           Calories Burned (Last 7 Days)
        --------------------------------*/
        $workoutStartDate = now()->subDays(6)->toDateString();
        $workoutEndDate   = now()->toDateString();

        $workouts = Workout::where('user_id', $user->id)
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
                'date'     => $date,
                'calories' => isset($workouts[$date]) ? (int) $workouts[$date]->calories : 0,
            ];
        }

        /* -------------------------------
           Calories Eaten
        --------------------------------*/
        $today     = now()->toDateString();
        $weekStart = now()->subDays(6)->toDateString();

        $todayCalories = Meal::where('user_id', $user->id)
            ->whereDate('meal_date', $today)
            ->sum('calories');

        $weeklyCalories = Meal::where('user_id', $user->id)
            ->whereDate('meal_date', '>=', $weekStart)
            ->sum('calories');

        /* -------------------------------
           Daily Goal
        --------------------------------*/
        $dailyGoal = $user->calorie_goal ?? 2200;

        /* -------------------------------
           Intake Chart (Last 7 Days)
        --------------------------------*/
        $meals = Meal::where('user_id', $user->id)
            ->whereDate('meal_date', '>=', $weekStart)
            ->whereDate('meal_date', '<=', $today)
            ->selectRaw('DATE(meal_date) as date, SUM(calories) as calories')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $calorieChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $calorieChart[] = [
                'date'     => $date,
                'calories' => isset($meals[$date]) ? (int) $meals[$date]->calories : 0,
            ];
        }

        /* -------------------------------
           Weekly Goal Progress
        --------------------------------*/
        $goalsCompleted = 0;
        foreach ($calorieChart as $day) {
            if ($day['calories'] >= $dailyGoal) {
                $goalsCompleted++;
            }
        }

        /* -------------------------------
           Dashboard Stats
        --------------------------------*/
        $stats = [
            'total_workouts'  => 0, // placeholder
            'total_calories'  => $weeklyCalories,
            'today_calories'  => $todayCalories,
            'daily_goal'      => $dailyGoal,
            'goal_percent'    => $dailyGoal > 0 ? round(($todayCalories / $dailyGoal) * 100) : 0,
            'goals_completed' => $goalsCompleted,
            'this_month'      => date('F Y'),
            'joined_date'     => $user->created_at->format('M d, Y'),
            'meal_streak'     => $user->meal_streak ?? 0,
            'meal_points'    => $user->meal_points ?? 0,
        ];

        return Inertia::render('Dashboard', [
            'user'              => $user,
            'stats'             => $stats,
            'calorieChart'      => $calorieChart,
            'calorieBurnedChart'=> $calorieBurnedChart,
        ]);
    }

    /**
     * Save Daily Calorie Goal
     */
    public function setCalorieGoal(Request $request)
    {
        $request->validate([
            'calorie_goal' => 'required|integer|min:500|max:10000',
        ]);

        $user = Auth::user();
        $user->calorie_goal = $request->calorie_goal;
        $user->save();

        return response()->json([
            'success' => true,
            'goal' => $user->calorie_goal,
        ]);
    }
}
