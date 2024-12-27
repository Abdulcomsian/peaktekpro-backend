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
        Schema::create('report_page_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_page_id');
            $table->foreign('report_page_id')->references('id')->on('report_pages')->onDelete('cascade');
            $table->json('json_data')->nullable();
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
        Schema::dropIfExists('report_page_data');
    }
};
