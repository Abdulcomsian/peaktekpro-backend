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
        Schema::create('material_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_order_id')->constrained('material_orders')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('option')->nullable();
            $table->string('unit')->nullable();
            $table->string('unit_cost')->nullable();
            $table->string('quantity')->nullable();
            $table->string('total')->nullable();
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
        Schema::dropIfExists('material_selections');
    }
};