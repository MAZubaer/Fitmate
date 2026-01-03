<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Workout extends Model
{
    use HasFactory;

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
        'muscle_group'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
