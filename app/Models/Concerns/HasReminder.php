<?php

namespace App\Models\Concerns;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A meal or workout that owns one reminder notification.
 *
 * Call syncReminder() after saving the model: it creates, moves or removes the
 * notification so there is always at most one reminder per meal or workout.
 */
trait HasReminder
{
    /** When the reminder should fire, or null when no reminder is wanted. */
    abstract public function reminderTime(): ?Carbon;

    /** The text shown in the app and in the reminder email. */
    abstract public function reminderMessage(): string;

    public function reminder(): HasOne
    {
        return $this->hasOne(Notification::class);
    }

    public function syncReminder(): void
    {
        $when = $this->reminderTime();
        $reminder = $this->reminder()->first();

        if (! $when) {
            // Keep reminders that already fired as history; drop pending ones.
            if ($reminder && $reminder->scheduled_at->isFuture()) {
                $reminder->delete();
            }

            return;
        }

        $reminder ??= new Notification(['user_id' => $this->user_id]);
        $rescheduled = ! $reminder->exists || ! $reminder->scheduled_at->equalTo($when);

        $reminder->message = $this->reminderMessage();
        $reminder->scheduled_at = $when;

        if ($rescheduled) {
            // A new time is a new reminder: show it as unread and email it again.
            $reminder->read = false;
            $reminder->emailed_at = null;
        }

        $this->reminder()->save($reminder);
    }

    /** Combine a date column and an "HH:MM[:SS]" time column into a Carbon instance. */
    protected static function combineDateTime(mixed $date, mixed $time): ?Carbon
    {
        if (! $date || ! $time) {
            return null;
        }

        $date = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : substr((string) $date, 0, 10);

        try {
            return Carbon::createFromFormat('Y-m-d H:i', $date.' '.substr((string) $time, 0, 5));
        } catch (\Throwable) {
            return null;
        }
    }
}
