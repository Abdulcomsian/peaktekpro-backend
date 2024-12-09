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
        Schema::create('material_order_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_order_id')->constrained('material_orders');
            $table->string('file_name')->nullable();
            $table->mediumText('notes')->nullable();
            $table->string('media_type')->nullable();
            $table->string('media_url')->nullable();
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
        Schema::dropIfExists('material_order_media');
    }
};
