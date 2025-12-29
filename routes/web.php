<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\AiMealAssistantController; // ← ADDED
use App\Http\Controllers\BmiController; // ← ADDED
use App\Http\Controllers\RecentActivityController; // ← ADDED
use App\Http\Controllers\BmiHistoryController; // ← ADDED
use App\Http\Controllers\StepController; // ← ADDED
use App\Http\Controllers\StepHistoryController; // ← ADDED
use App\Http\Controllers\WaterController; // ← ADDED
use App\Http\Controllers\WaterHistoryController; // ← ADDED
use Illuminate\Foundation\Application;


// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Workout page
    Route::get('/workout', function () {
        return Inertia::render('Workout');
    })->name('workout.index');


    // Workout CRUD API
    Route::get('/workouts-data', [WorkoutController::class, 'index']);
    Route::post('/workouts-data', [WorkoutController::class, 'store']);
    Route::put('/workouts-data/{workout}', [WorkoutController::class, 'update']);
    Route::delete('/workouts-data/{workout}', [WorkoutController::class, 'destroy']);

    // Meals CRUD
    Route::resource('meals', MealController::class);

    // Other frontend pages

    // Meals CRUD
    Route::resource('meals', MealController::class);

    // Meal Assistant page

    Route::get('/meal-assistant', function () {
        return Inertia::render('MealAssistant');
    })->name('meal.assistant');

    // AI Meal Assistant backend API
    Route::post('/ai/meal-assistant', [AiMealAssistantController::class, 'generate']) // ← ADDED
        ->name('ai.meal.assistant');

    Route::get('/notifications', function () {
        return Inertia::render('Notifications');
    })->name('notifications');
    Route::get('/workouts-data', [WorkoutController::class, 'index']);     // # ← ADDED

    // Store new workout
    Route::post('/workouts-data', [WorkoutController::class, 'store']);     // # ← ADDED

    // BMI update
    Route::post('/bmi', [BmiController::class, 'store']); // ← ADDED

    // Recent activity
    Route::get('/recent-activity', [RecentActivityController::class, 'index']); // ← ADDED

    // BMI history
    Route::get('/bmi-history', [BmiHistoryController::class, 'index']); // ← ADDED

    // Steps
    Route::post('/steps', [StepController::class, 'store']); // ← ADDED
    Route::get('/steps/today', [StepController::class, 'today']); // ← ADDED
    Route::get('/steps-history', [StepHistoryController::class, 'index']); // ← ADDED

    // Water intake
    Route::post('/water', [WaterController::class, 'store']); // ← ADDED
    Route::get('/water/today', [WaterController::class, 'today']); // ← ADDED
    Route::get('/water-history', [WaterHistoryController::class, 'index']);
});

require __DIR__.'/auth.php';