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
        Schema::create('material_order_confirmation_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_order_confirmation_id')->constrained('material_order_confirmations')->onDelete('cascade');
            $table->string('image_url');
            $table->string('file_name')->nullable();
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
        Schema::dropIfExists('material_order_confirmation_media');
    }
};
