<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_chatbot_info', function (Blueprint $table) {
            $table->commonFields();
            $table->foreignId('service_id')->unique()->constrained('services')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_chatbot_info');
    }
};
