<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cash_box_reports', function (Blueprint $table) {
            $table->enum('type', ['Bordado', 'Estampado'])->default('Bordado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_box_reports', function (Blueprint $table) {
            //
        });
    }
};