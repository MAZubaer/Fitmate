<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MealController extends Controller
{
    /**
     * Display a listing of meals.
     */
    public function index()
    {
        $meals = Meal::where('user_id', auth()->id())
                     ->orderBy('meal_date', 'desc')
                     ->get();

        return Inertia::render('Meals/Index', [
            'meals' => $meals
        ]);
    }

    /**
     * Show the form for creating a new meal.
     */
    public function create()
    {
        return Inertia::render('Meals/Create');
    }

    /**
     * Store a newly created meal in storage.
     * ✅ UPDATED WITH STREAK + POINTS LOGIC
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'calories' => 'nullable|integer',
            'meal_date' => 'nullable|date',
            'meal_time' => 'nullable',
        ]);

        $user = auth()->user();
        $today = now()->toDateString();

        // Save meal
        Meal::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'calories' => $request->calories,
            'meal_date' => $request->meal_date ?? $today,
            'meal_time' => $request->meal_time,
        ]);

        // 🔥 STREAK LOGIC
        if ($user->last_meal_date === now()->subDay()->toDateString()) {
            $user->meal_streak += 1; // continue streak
        } else {
            $user->meal_streak = 1; // reset streak
        }

        // ⭐ POINTS LOGIC
        $user->meal_points += 10;
        $user->last_meal_date = $today;
        $user->save();

        return redirect()->route('meals.index')
            ->with('success', 'Meal saved! Streak and points updated 🎉');
    }

    /**
     * Show the form for editing a meal.
     */
    public function edit(Meal $meal)
    {
        $this->authorizeMeal($meal);

        return Inertia::render('Meals/Edit', [
            'meal' => $meal
        ]);
    }

    /**
     * Update the specified meal.
     */
    public function update(Request $request, Meal $meal)
    {
        $this->authorizeMeal($meal);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'calories' => 'nullable|integer',
            'meal_date' => 'nullable|date',
            'meal_time' => 'nullable',
        ]);

        $meal->update($request->only(['name','description','calories','meal_date','meal_time']));

        return redirect()->route('meals.index')->with('success', 'Meal updated successfully.');
    }

    /**
     * Remove the specified meal from storage.
     */
    public function destroy(Meal $meal)
    {
        $this->authorizeMeal($meal);

        $meal->delete();

        return redirect()->route('meals.index')->with('success', 'Meal deleted.');
    }

    /**
     * Authorization helper to ensure user owns the meal.
     */
    private function authorizeMeal(Meal $meal)
    {
        if ($meal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
