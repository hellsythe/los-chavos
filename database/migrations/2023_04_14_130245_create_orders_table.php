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
        Schema::create('orders', function (Blueprint $table) {
            $table->commonFields();
            $table->foreignId('client_id');
            $table->foreignId('garment_id');
            $table->foreignId('authorized_by')->nullable()->constrained('users');
            $table->integer('garment_amount');
            $table->double('total', 8, 2);
            $table->double('missing_payment', 8, 2)->nullable();
            $table->string('order_number')->nullable();
            $table->date('deadline');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
