<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'nickname',
        'age',
        'gender',
        'calorie_goal',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    /**
     * The fields the frontend needs. This is what gets sent to the browser,
     * so never add secrets here.
     */
    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'nickname' => $this->nickname,
            'age' => $this->age,
            'gender' => $this->gender,
            'calorie_goal' => $this->calorie_goal,
            'meal_streak' => $this->currentMealStreak(),
            'meal_points' => (int) $this->meal_points,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            // Google-only accounts have no password; the profile page adapts to that.
            'has_password' => $this->password !== null,
        ];
    }

    /**
     * The stored streak, or 0 when the user hasn't logged a meal today or yesterday
     * (the stored value is only refreshed when meals change, so it can go stale).
     */
    public function currentMealStreak(): int
    {
        $last = $this->last_meal_date ? substr((string) $this->last_meal_date, 0, 10) : null;

        return $last && $last >= Carbon::yesterday()->toDateString() ? (int) $this->meal_streak : 0;
    }

    /**
     * Recalculate meal_streak and last_meal_date from the user's meals.
     *
     * The streak counts consecutive days with at least one meal, ending today,
     * or yesterday if nothing is logged yet today. Logging several meals on the
     * same day counts that day once. Call save() afterwards.
     */
    public function refreshMealStreak(): void
    {
        $today = Carbon::today();

        $days = $this->meals()
            ->whereNotNull('meal_date')
            ->whereDate('meal_date', '<=', $today->toDateString())
            ->pluck('meal_date')
            ->map(fn ($date) => substr((string) $date, 0, 10))
            ->unique()
            ->sortDesc()
            ->values();

        $this->last_meal_date = $days->first();

        $streak = 0;
        $expected = $days->first() === $today->toDateString() ? $today : $today->copy()->subDay();

        foreach ($days as $day) {
            if ($day !== $expected->toDateString()) {
                break;
            }
            $streak++;
            $expected->subDay();
        }

        $this->meal_streak = $streak;
    }
}
