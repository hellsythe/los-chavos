<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->timestamp('bot_last_inbound_at')->nullable()->after('bot');
            $table->timestamp('bot_pending_response_at')->nullable()->after('bot_last_inbound_at');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('processed_by_bot_at')->nullable()->after('direction');
            $table->index('processed_by_bot_at');
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['bot_last_inbound_at', 'bot_pending_response_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['processed_by_bot_at']);
            $table->dropColumn('processed_by_bot_at');
        });
    }
};
