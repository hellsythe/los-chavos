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
        Schema::create('order_update_designs', function (Blueprint $table) {
            $table->commonFields();
            $table->foreignId('order_detail_id');
            $table->foreignId('design_id');
            $table->foreignId('old_design_id')->constrained('designs');;
            $table->double('price', 8, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_update_designs');
    }
};
