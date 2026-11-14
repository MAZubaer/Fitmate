<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails the reminders that just became due.
 *
 * Every meal or workout with a time owns one notification (see HasReminder),
 * so this only has to find due notifications that haven't been emailed yet.
 * The in-app notification already exists; this command never creates another.
 */
class SendReminderEmails extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Send meal & workout reminder emails';

    /**
     * How far back to look. Reminders are sent once (emailed_at), so a wider
     * window just lets a short scheduler outage catch up instead of skipping.
     */
    private const CATCH_UP_MINUTES = 15;

    public function handle(): int
    {
        $now = now();
        $sent = 0;

        Notification::query()
            ->whereNull('emailed_at')
            ->whereBetween('scheduled_at', [$now->copy()->subMinutes(self::CATCH_UP_MINUTES), $now])
            ->where(fn ($q) => $q->whereNotNull('meal_id')->orWhereNotNull('workout_id'))
            ->with(['user', 'workout'])
            ->chunkById(100, function ($reminders) use (&$sent) {
                foreach ($reminders as $reminder) {
                    // Mark first: a failing mail server must not cause a resend every minute.
                    $reminder->forceFill(['emailed_at' => now()])->save();

                    if (! $reminder->user || $reminder->workout?->completed) {
                        continue;
                    }

                    try {
                        $subject = $reminder->workout_id ? '💪 FitMate Workout Reminder' : '🍽 FitMate Meal Reminder';
                        Mail::raw($reminder->message, fn ($mail) => $mail->to($reminder->user->email)->subject($subject));
                        $sent++;
                    } catch (\Throwable $e) {
                        // One bad address or mail outage shouldn't stop everyone else's reminders.
                        Log::warning('Reminder email failed', ['notification_id' => $reminder->id, 'error' => $e->getMessage()]);
                    }
                }
            });

        $this->info("Sent {$sent} reminder(s) at {$now}");

        return self::SUCCESS;
    }
}
