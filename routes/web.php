<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MealController; // 
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // New app routes with navbar
    Route::get('/workout', function () {
        return Inertia::render('Workout');
    })->name('workout.index');
    
    // Meals CRUD
    Route::resource('meals', MealController::class);

    Route::get('/meal-assistant', function () {
        return Inertia::render('MealAssistant');
    })->name('meal.assistant');

    Route::get('/notifications', function () {
        return Inertia::render('Notifications');
    })->name('notifications');
});

require __DIR__.'/auth.php';
