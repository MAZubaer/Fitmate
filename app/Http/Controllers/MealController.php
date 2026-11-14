<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MealController extends Controller
{
    /** Points awarded for logging a meal (and removed when it is deleted). */
    private const POINTS_PER_MEAL = 10;

    public function index()
    {
        $meals = Meal::where('user_id', auth()->id())
            ->orderBy('meal_date', 'desc')
            ->get();

        return Inertia::render('Meals/Index', [
            'meals' => $meals,
        ]);
    }

    public function create()
    {
        return Inertia::render('Meals/Create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = $request->user();

        $meal = Meal::create([
            ...$data,
            'user_id' => $user->id,
            'meal_date' => $data['meal_date'] ?? now()->toDateString(),
        ]);
        $meal->syncReminder();

        $user->meal_points += self::POINTS_PER_MEAL;
        $user->refreshMealStreak();
        $user->save();

        return redirect()->route('meals.index')
            ->with('success', 'Meal saved! Streak and points updated 🎉');
    }

    public function edit(Meal $meal)
    {
        $this->authorizeMeal($meal);

        return Inertia::render('Meals/Edit', [
            'meal' => $meal,
        ]);
    }

    public function update(Request $request, Meal $meal)
    {
        $this->authorizeMeal($meal);

        $data = $this->validated($request);
        $meal->update([
            ...$data,
            'meal_date' => $data['meal_date'] ?? $meal->meal_date,
        ]);
        $meal->syncReminder();

        // The date may have moved, which can change the streak.
        $user = $request->user();
        $user->refreshMealStreak();
        $user->save();

        return redirect()->route('meals.index')->with('success', 'Meal updated successfully.');
    }

    public function destroy(Request $request, Meal $meal)
    {
        $this->authorizeMeal($meal);

        // Its reminder notification is removed by the foreign key cascade.
        $meal->delete();

        $user = $request->user();
        $user->meal_points = max(0, $user->meal_points - self::POINTS_PER_MEAL);
        $user->refreshMealStreak();
        $user->save();

        return redirect()->route('meals.index')
            ->with('success', 'Meal deleted. Points & streak updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'calories' => 'nullable|integer|min:0|max:20000',
            'meal_date' => 'nullable|date_format:Y-m-d',
            // "HH:MM" from the time picker, or "HH:MM:SS" when an existing value is sent back.
            'meal_time' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
        ], [
            'meal_time.regex' => 'Enter a valid time, like 13:30.',
        ]);
    }

    private function authorizeMeal(Meal $meal): void
    {
        if ($meal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
