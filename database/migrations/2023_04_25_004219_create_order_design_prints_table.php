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
        Schema::create('order_design_prints', function (Blueprint $table) {
            $table->commonFields();
            $table->foreignId('order_detail_id');
            $table->foreignId('design_print_id')->nullable();
            $table->double('price', 8, 2);
            $table->boolean('save_design');
            $table->boolean('if_new_design');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_design_prints');
    }
};
