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
        Schema::create('materail_drop_downs', function (Blueprint $table) {
            $table->id();
            $table->string('order_key')->nullable();
            $table->string('quantity')->nullable();
            $table->json('color')->nullable(); // Store multiple colors as JSON
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
        Schema::dropIfExists('materail_drop_downs');
    }
};
