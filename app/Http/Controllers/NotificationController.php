<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        return Notification::where('user_id', auth()->id())
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at', 'desc')
            ->get();
    }

    public function unreadCount()
    {
        return Notification::where('user_id', auth()->id())
            ->where('read', false)
            ->where('scheduled_at', '<=', now())
            ->count();
    }

    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['read' => true]);
        return response()->json(['success' => true]);
    }
}
