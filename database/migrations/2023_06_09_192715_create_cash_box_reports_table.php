<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_box_reports', function (Blueprint $table) {
            $table->commonFields();
            $table->double('out_cash')->nullable();
            $table->double('calculate_cash')->nullable();
            $table->double('missing_cash')->nullable();
            $table->double('real_cash')->nullable();
            $table->double('out_card')->nullable();
            $table->double('calculate_card')->nullable();
            $table->double('missing_card')->nullable();
            $table->double('real_card')->nullable();
            $table->date('start');
            $table->date('finish');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_box_reports');
    }
};
