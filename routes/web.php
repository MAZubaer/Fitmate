<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkoutController; 
use App\Http\Controllers\MealController; 


// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



// Dashboard (requires auth)
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
    Route::get('/meal-assistant', function () {
        return Inertia::render('MealAssistant');
    })->name('meal.assistant');

    Route::get('/notifications', function () {
        return Inertia::render('Notifications');
    })->name('notifications');

    
});

require __DIR__.'/auth.php';
