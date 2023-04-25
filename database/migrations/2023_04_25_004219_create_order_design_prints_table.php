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
            $table->foreignId('design_print_id');
            $table->double('price', 8, 2);
            $table->boolean('saveDesign');
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
