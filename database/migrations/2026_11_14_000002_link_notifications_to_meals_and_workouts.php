<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Each meal or workout now owns exactly one reminder notification:
 * - meal_id / workout_id tie the notification to its source, so editing the
 *   meal or workout moves the reminder and deleting it removes the reminder.
 * - emailed_at records that the reminder email went out, so it is sent once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('meal_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_id')->nullable()->after('meal_id')->constrained()->cascadeOnDelete();
            $table->timestamp('emailed_at')->nullable()->after('scheduled_at');
            $table->index(['emailed_at', 'scheduled_at']);
        });

        // Reminders that are already due were handled by the old command; don't email them again.
        DB::table('notifications')->where('scheduled_at', '<=', now())->update(['emailed_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['emailed_at', 'scheduled_at']);
            $table->dropConstrainedForeignId('workout_id');
            $table->dropConstrainedForeignId('meal_id');
            $table->dropColumn('emailed_at');
        });
    }
};
