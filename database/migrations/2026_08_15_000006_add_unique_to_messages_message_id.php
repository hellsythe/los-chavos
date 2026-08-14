<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $duplicates = DB::table('messages')
            ->whereNotNull('message_id')
            ->where('message_id', '!=', '')
            ->select('message_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as c'))
            ->groupBy('message_id')
            ->having('c', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('messages')
                ->where('message_id', $dup->message_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Schema::table('messages', function (Blueprint $table) {
            $table->unique('message_id', 'messages_message_id_unique');
            $table->index('chat_id', 'messages_chat_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropUnique('messages_message_id_unique');
            $table->dropIndex('messages_chat_id_index');
        });
    }
};
