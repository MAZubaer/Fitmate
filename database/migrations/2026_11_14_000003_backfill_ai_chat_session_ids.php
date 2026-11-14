<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Chats saved before sessions existed have no session_id. Give each one its
 * own session once, here, instead of on every GET /ai/chat-history request.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_chats')->whereNull('session_id')->orderBy('id')->each(function ($row) {
            DB::table('ai_chats')->where('id', $row->id)->update(['session_id' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        // Nothing to undo: the generated session IDs are harmless.
    }
};
