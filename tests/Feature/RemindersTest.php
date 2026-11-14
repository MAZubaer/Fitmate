<?php

namespace Tests\Feature;

use App\Models\Meal;
use App\Models\Notification;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RemindersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.default' => 'array']);
        Carbon::setTestNow('2026-11-14 12:00:00');
    }

    private function sentCount(): int
    {
        return Mail::mailer('array')->getSymfonyTransport()->messages()->count();
    }

    private function workoutPayload(array $overrides = []): array
    {
        return [
            'name' => 'Squats', 'sets' => 3, 'reps' => 10, 'calories' => 100,
            'date' => '2026-11-14', 'time' => '12:30', ...$overrides,
        ];
    }

    public function test_each_meal_owns_one_reminder_that_follows_edits_and_deletes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/meals', ['name' => 'Lunch', 'meal_date' => '2026-11-14', 'meal_time' => '13:00']);
        $meal = Meal::first();
        $this->assertSame(1, Notification::count());
        $this->assertSame('2026-11-14 13:00', $meal->reminder->scheduled_at->format('Y-m-d H:i'));

        $this->actingAs($user)->put("/meals/{$meal->id}", ['name' => 'Late lunch', 'meal_date' => '2026-11-14', 'meal_time' => '14:15']);
        $this->assertSame(1, Notification::count());
        $reminder = Notification::first();
        $this->assertSame('2026-11-14 14:15', $reminder->scheduled_at->format('Y-m-d H:i'));
        $this->assertStringContainsString('Late lunch', $reminder->message);

        $this->actingAs($user)->delete("/meals/{$meal->id}");
        $this->assertSame(0, Notification::count());
    }

    public function test_invalid_meal_time_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/meals', ['name' => 'Lunch', 'meal_time' => 'noon'])
            ->assertSessionHasErrors('meal_time');
        $this->assertSame(0, Meal::count());
    }

    public function test_a_due_reminder_is_emailed_once_without_creating_another_notification(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/workouts-data', $this->workoutPayload())->assertCreated();

        Carbon::setTestNow('2026-11-14 12:31:00');
        $this->artisan('reminders:send')->assertSuccessful();
        $this->artisan('reminders:send')->assertSuccessful();

        $this->assertSame(1, $this->sentCount());
        $this->assertSame(1, Notification::count());
        $this->assertNotNull(Notification::first()->emailed_at);
    }

    public function test_a_repeated_workout_name_still_gets_its_own_reminder(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/workouts-data', $this->workoutPayload(['time' => '12:30']));
        $this->actingAs($user)->postJson('/workouts-data', $this->workoutPayload(['time' => '18:00']));

        Carbon::setTestNow('2026-11-14 12:31:00');
        $this->artisan('reminders:send');
        Carbon::setTestNow('2026-11-14 18:01:00');
        $this->artisan('reminders:send');

        $this->assertSame(2, $this->sentCount());
    }

    public function test_completing_a_workout_cancels_its_pending_reminder(): void
    {
        $user = User::factory()->create();
        $id = $this->actingAs($user)->postJson('/workouts-data', $this->workoutPayload())->json('id');

        $this->actingAs($user)->putJson("/workouts-data/{$id}/complete")->assertOk();
        $this->assertTrue(Workout::find($id)->completed);
        $this->assertSame(0, Notification::count());

        Carbon::setTestNow('2026-11-14 12:31:00');
        $this->artisan('reminders:send');
        $this->assertSame(0, $this->sentCount());
    }
}
