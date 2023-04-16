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
        Schema::create('order_details', function (Blueprint $table) {
            $table->commonFields();
            $table->foreignId('order_id');
            $table->foreignId('service_id');
            $table->foreignId('subservice_id');
            $table->double('price', 8, 2);
            $table->double('total', 8, 2);
            $table->string('point_x');
            $table->string('point_y');
            $table->string('comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
};
