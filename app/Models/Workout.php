<?php

namespace App\Models;

use App\Models\Concerns\HasReminder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory, HasReminder;

    protected $fillable = [
        'user_id',
        'name',
        'sets',
        'reps',
        'date',
        'time',
        'calories',
        'completed',
        'duration',
        'muscle_group',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reminderTime(): ?Carbon
    {
        // A finished workout needs no reminder.
        return $this->completed ? null : static::combineDateTime($this->date, $this->time);
    }

    public function reminderMessage(): string
    {
        return "🏋️ Time for your workout: {$this->name}";
    }
}
