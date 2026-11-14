<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'meal_id',
        'workout_id',
        'message',
        'read',
        'scheduled_at',
        'emailed_at',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
            'scheduled_at' => 'datetime',
            'emailed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }
}
