<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Meal;
use App\Models\Workout;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendReminderEmails extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send meal & workout reminder emails';

    public function handle()
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->subMinutes(2);

        // ===== MEALS =====
        foreach (Meal::all() as $meal) {
            $scheduled = Carbon::parse($meal->meal_date . ' ' . $meal->meal_time);

            if ($scheduled->betweenIncluded($windowStart, $now)) {
                $user = User::find($meal->user_id);
                if (!$user) continue;

                $msg = "Meal Reminder: " . $meal->name;

                if (!Notification::where('user_id', $user->id)
                    ->where('message', $msg)
                    ->exists()) {

                    Mail::raw("Time for your meal: {$meal->name}", function ($mail) use ($user) {
                        $mail->to($user->email)
                             ->subject("🍽 FitMate Meal Reminder");
                    });

                    Notification::create([
                        'user_id' => $user->id,
                        'message' => $msg,
                        'scheduled_at' => $scheduled,
                        'read' => false
                    ]);
                }
            }
        }

        // ===== WORKOUTS =====
        foreach (Workout::all() as $workout) {
            $scheduled = Carbon::parse($workout->date . ' ' . $workout->time);

            if ($scheduled->betweenIncluded($windowStart, $now)) {
                $user = User::find($workout->user_id);
                if (!$user) continue;

                $msg = "Workout Reminder: " . $workout->name;

                if (!Notification::where('user_id', $user->id)
                    ->where('message', $msg)
                    ->exists()) {

                    Mail::raw("Time for your workout: {$workout->name}", function ($mail) use ($user) {
                        $mail->to($user->email)
                             ->subject("💪 FitMate Workout Reminder");
                    });

                    Notification::create([
                        'user_id' => $user->id,
                        'message' => $msg,
                        'scheduled_at' => $scheduled,
                        'read' => false
                    ]);
                }
            }
        }

        $this->info("Reminder check completed at " . $now);
    }
}
