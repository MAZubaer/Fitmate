<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

use App\Models\AiChat;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\AiMealAssistantController;
use App\Http\Controllers\BmiController;
use App\Http\Controllers\RecentActivityController;
use App\Http\Controllers\BmiHistoryController;
use App\Http\Controllers\StepController;
use App\Http\Controllers\StepHistoryController;
use App\Http\Controllers\WaterController;
use App\Http\Controllers\WaterHistoryController;
use App\Http\Controllers\AnalyticsExportController;
use App\Http\Controllers\NotificationController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Workouts
    |--------------------------------------------------------------------------
    */

    Route::get('/workout', fn () => Inertia::render('Workout'))->name('workout.index');

    Route::get('/workouts-data', [WorkoutController::class, 'index']);
    Route::post('/workouts-data', [WorkoutController::class, 'store']);
    Route::put('/workouts-data/{workout}', [WorkoutController::class, 'update']);
    Route::put('/workouts-data/{workout}/complete', [WorkoutController::class, 'complete']);
    Route::delete('/workouts-data/{workout}', [WorkoutController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Meals
    |--------------------------------------------------------------------------
    */

    Route::resource('meals', MealController::class);
    Route::resource('meals', MealController::class); // kept for teammate safety

    /*
    |--------------------------------------------------------------------------
    | Meal Assistant
    |--------------------------------------------------------------------------
    */

    Route::get('/meal-assistant', fn () => Inertia::render('MealAssistant'))->name('meal.assistant');

    Route::post('/ai/meal-assistant', [AiMealAssistantController::class, 'generate'])
        ->name('ai.meal.assistant');

    /*
    |--------------------------------------------------------------------------
    | AI Chat History
    |--------------------------------------------------------------------------
    */

    Route::get('/ai/chat-history', function () {
        AiChat::where('user_id', auth()->id())
            ->whereNull('session_id')
            ->get()
            ->each(function ($row) {
                $row->session_id = (string) Str::uuid();
                $row->save();
            });

        return AiChat::select('session_id', 'user_message', 'created_at')
            ->where('user_id', auth()->id())
            ->whereNotNull('session_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('session_id')
            ->values();
    });

    Route::get('/ai/chat/{session}', function ($session) {
        return AiChat::where('user_id', auth()->id())
            ->where('session_id', $session)
            ->orderBy('created_at')
            ->get();
    });

    Route::delete('/ai/chat/{session}', function ($session) {
        AiChat::where('user_id', auth()->id())
            ->where('session_id', $session)
            ->delete();

        return response()->json(['success' => true]);
    });

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */

    Route::get('/analytics/export', fn () => Inertia::render('AnalyticsExport'));
    Route::get('/analytics/export/csv', [AnalyticsExportController::class, 'csv']);
    Route::get('/analytics/export/pdf', [AnalyticsExportController::class, 'pdf']);

    /*
    |--------------------------------------------------------------------------
    | User Settings
    |--------------------------------------------------------------------------
    */

    Route::post('/user/calorie-goal', function (Request $request) {
        $request->validate(['goal' => 'required|integer|min:1000|max:5000']);
        auth()->user()->update([
            'calorie_goal' => $request->goal
        ]);
        return response()->json(['success' => true]);
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications', fn () => Inertia::render('Notifications'))->name('notifications');

    Route::get('/api/notifications', [NotificationController::class, 'index']);
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/api/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    /*
    |--------------------------------------------------------------------------
    | Health Tracking
    |--------------------------------------------------------------------------
    */

    Route::post('/bmi', [BmiController::class, 'store']);
    Route::get('/recent-activity', [RecentActivityController::class, 'index']);
    Route::get('/bmi-history', [BmiHistoryController::class, 'index']);

    Route::post('/steps', [StepController::class, 'store']);
    Route::get('/steps/today', [StepController::class, 'today']);
    Route::get('/steps-history', [StepHistoryController::class, 'index']);

    Route::post('/water', [WaterController::class, 'store']);
    Route::get('/water/today', [WaterController::class, 'today']);
    Route::get('/water-history', [WaterHistoryController::class, 'index']);
});

require __DIR__ . '/auth.php';
