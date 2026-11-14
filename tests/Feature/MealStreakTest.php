<?php

namespace Tests\Feature;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MealStreakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-14 12:00:00');
    }

    private function logMeal(User $user, string $date): void
    {
        $this->actingAs($user)->post('/meals', ['name' => 'Rice', 'calories' => 300, 'meal_date' => $date])
            ->assertRedirect('/meals');
    }

    public function test_second_meal_on_the_same_day_keeps_the_streak(): void
    {
        $user = User::factory()->create();

        $this->logMeal($user, '2026-11-13');
        $this->logMeal($user, '2026-11-14');
        $this->logMeal($user, '2026-11-14');

        $user->refresh();
        $this->assertSame(2, $user->meal_streak);
        $this->assertSame(30, $user->meal_points);
    }

    public function test_a_gap_resets_the_streak(): void
    {
        $user = User::factory()->create();

        $this->logMeal($user, '2026-11-10');
        $this->logMeal($user, '2026-11-14');

        $this->assertSame(1, $user->refresh()->meal_streak);
    }

    public function test_deleting_a_meal_uses_the_same_streak_rules_as_adding(): void
    {
        $user = User::factory()->create();

        $this->logMeal($user, '2026-11-12');
        $this->logMeal($user, '2026-11-13');
        $this->logMeal($user, '2026-11-14');
        $this->logMeal($user, '2026-11-14');

        // Remove one of today's two meals: today still counts, so the streak stays 3.
        $meal = Meal::where('meal_date', '2026-11-14')->first();
        $this->actingAs($user)->delete("/meals/{$meal->id}")->assertRedirect('/meals');
        $this->assertSame(3, $user->refresh()->meal_streak);

        // Remove the other one: the streak continues from yesterday (2), as adding would.
        $meal = Meal::where('meal_date', '2026-11-14')->first();
        $this->actingAs($user)->delete("/meals/{$meal->id}");
        $user->refresh();
        $this->assertSame(2, $user->meal_streak);
        $this->assertSame(20, $user->meal_points);
    }

    public function test_a_stale_streak_is_shown_as_zero(): void
    {
        $user = User::factory()->create();
        $this->logMeal($user, '2026-11-14');

        Carbon::setTestNow('2026-11-20 12:00:00');

        $this->assertSame(0, $user->refresh()->currentMealStreak());
    }

    public function test_meal_show_route_does_not_exist(): void
    {
        $user = User::factory()->create();
        $this->logMeal($user, '2026-11-14');
        $meal = Meal::first();

        $this->actingAs($user)->get("/meals/{$meal->id}")->assertStatus(405);
    }
}
