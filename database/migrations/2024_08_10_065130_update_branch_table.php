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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable();
        });

        Schema::table('cash_box_reports', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
