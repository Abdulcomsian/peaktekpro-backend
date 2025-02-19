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
        Schema::create('authorization_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authorization_section_id')->constrained('authorization_sections')->onDelete('cascade');
            $table->string('item');
            $table->integer('quantity');
            $table->string('price');
            $table->string('line_total');
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
        Schema::dropIfExists('authorization_items');
    }
};
