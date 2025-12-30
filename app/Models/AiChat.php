<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChat extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',     // ✅ REQUIRED
        'user_message',
        'ai_reply',
    ];
}
