<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
use Illuminate\Foundation\Application;
use App\Models\AiChat;
use Illuminate\Support\Str;
use App\Http\Controllers\AnalyticsExportController;


// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Workout
    Route::get('/workout', fn () => Inertia::render('Workout'))->name('workout.index');

    Route::get('/workouts-data', [WorkoutController::class, 'index']);
    Route::post('/workouts-data', [WorkoutController::class, 'store']);
    Route::put('/workouts-data/{workout}', [WorkoutController::class, 'update']);
    Route::put('/workouts-data/{workout}/complete', [WorkoutController::class, 'complete']);

    Route::delete('/workouts-data/{workout}', [WorkoutController::class, 'destroy']);

    // Meals
    Route::resource('meals', MealController::class);
    Route::resource('meals', MealController::class); // kept for teammate safety

    // Meal Assistant UI
    Route::get('/meal-assistant', fn () => Inertia::render('MealAssistant'))->name('meal.assistant');

    // AI
    Route::post('/ai/meal-assistant', [AiMealAssistantController::class, 'generate'])
        ->name('ai.meal.assistant');

    // 🧠 ChatGPT-style History (FIXED)
    Route::get('/ai/chat-history', function () {

        // Backfill NULL session IDs so old chats show up
        AiChat::where('user_id', auth()->id())
            ->whereNull('session_id')
            ->get()
            ->each(function ($row) {
                $row->session_id = (string) Str::uuid();
                $row->save();
            });

        // One latest message per session (ChatGPT-style)
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
    \App\Models\AiChat::where('user_id', auth()->id())
        ->where('session_id', $session)
        ->delete();

    return response()->json(['success' => true]);
    });


    Route::get('/analytics/export', fn () => Inertia::render('AnalyticsExport'));
    Route::get('/analytics/export/csv', [AnalyticsExportController::class, 'csv']);
    Route::get('/analytics/export/pdf', [AnalyticsExportController::class, 'pdf']);

    // Update calorie goals
    Route::post('/user/calorie-goal', function (Request $request) {
    $request->validate(['goal' => 'required|integer|min:1000|max:5000']);
    auth()->user()->update([
        'calorie_goal' => $request->goal
    ]);
    return response()->json(['success' => true]);
    });

    // Notifications
    Route::get('/notifications', fn () => Inertia::render('Notifications'))->name('notifications');

    // Health tracking
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

require __DIR__.'/auth.php';
