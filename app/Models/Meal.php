<?php

namespace App\Models;

use App\Models\Concerns\HasReminder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory, HasReminder;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'calories',
        'meal_date',
        'meal_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reminderTime(): ?Carbon
    {
        return static::combineDateTime($this->meal_date, $this->meal_time);
    }

    public function reminderMessage(): string
    {
        return "🍽️ Time for your meal: {$this->name}";
    }
}
