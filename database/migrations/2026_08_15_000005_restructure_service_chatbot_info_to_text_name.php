<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_chatbot_info', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropUnique(['service_id']);
            $table->dropColumn('service_id');
            $table->string('name')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('service_chatbot_info', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->foreignId('service_id')->unique()->constrained('services')->cascadeOnDelete();
        });
    }
};
