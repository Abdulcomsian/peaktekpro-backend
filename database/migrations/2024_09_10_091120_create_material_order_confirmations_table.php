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
        Schema::create('material_order_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_order_id')->constrained('material_orders')->onDelete('cascade');
            $table->boolean('status')->default(0);
            $table->boolean('confirmation_email')->default(0);
            $table->boolean('material_order_confirmation_email')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_order_confirmations');
    }
};
