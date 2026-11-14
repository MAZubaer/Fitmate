<?php

namespace Tests\Feature;

use App\Models\Meal;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_escapes_commas_quotes_and_formulas(): void
    {
        $user = User::factory()->create();
        Meal::create(['user_id' => $user->id, 'name' => 'Rice, "beans"', 'calories' => 500, 'meal_date' => '2026-11-10']);
        Meal::create(['user_id' => $user->id, 'name' => '=HYPERLINK("x")', 'calories' => 1, 'meal_date' => '2026-11-10']);

        $csv = $this->actingAs($user)->get('/analytics/export/csv?from=2026-11-01&to=2026-11-30')
            ->assertOk()->getContent();

        $rows = array_map('str_getcsv', array_filter(explode("\n", $csv)));
        $names = array_column($rows, 2);
        $this->assertContains('Rice, "beans"', $names);
        $this->assertContains('\'=HYPERLINK("x")', $names);
        $this->assertCount(6, $rows[1]);
    }

    public function test_workouts_are_filtered_by_their_scheduled_date(): void
    {
        $user = User::factory()->create();
        Workout::create(['user_id' => $user->id, 'name' => 'Inside', 'sets' => 1, 'reps' => 1, 'calories' => 50, 'date' => '2026-11-10', 'time' => '08:00']);
        Workout::create(['user_id' => $user->id, 'name' => 'Outside', 'sets' => 1, 'reps' => 1, 'calories' => 50, 'date' => '2026-12-10', 'time' => '08:00']);

        $csv = $this->actingAs($user)->get('/analytics/export/csv?from=2026-11-01&to=2026-11-30')->getContent();

        $this->assertStringContainsString('Inside', $csv);
        $this->assertStringNotContainsString('Outside', $csv);
    }

    public function test_dates_are_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/analytics/export/csv?from=2026-11-30&to=2026-11-01')
            ->assertStatus(422)->assertJsonValidationErrors('to');
        $this->actingAs($user)->getJson('/analytics/export/pdf')
            ->assertStatus(422)->assertJsonValidationErrors(['from', 'to']);
    }
}
