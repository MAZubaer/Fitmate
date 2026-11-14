<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Application;

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
use App\Http\Controllers\MoodController; // 👈 ADDED

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
    
Route::post('/calorie-goal', [DashboardController::class, 'setCalorieGoal'])
    ->middleware('auth');



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

    Route::resource('meals', MealController::class)->except('show');

    

    /*
    |--------------------------------------------------------------------------
    | Meal Assistant
    |--------------------------------------------------------------------------
    */

    Route::get('/meal-assistant', fn () => Inertia::render('MealAssistant'))->name('meal.assistant');

    // Rate limited (see AppServiceProvider) so one account can't run up the Groq bill.
    Route::post('/ai/meal-assistant', [AiMealAssistantController::class, 'generate'])
        ->middleware('throttle:ai')
        ->name('ai.meal.assistant');

    /*
    |--------------------------------------------------------------------------
    | AI Chat History
    |--------------------------------------------------------------------------
    */

    Route::get('/ai/chat-history', function () {
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

    // 🧩 Mood Tracker
    Route::post('/mood', [MoodController::class, 'store']);
    Route::get('/mood/today', [MoodController::class, 'today']);
});

require __DIR__ . '/auth.php';
