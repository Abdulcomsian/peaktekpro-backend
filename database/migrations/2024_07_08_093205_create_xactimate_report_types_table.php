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
        Schema::create('xactimate_report_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xactimate_report_id')->constrained('xactimate_reports')->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
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
        Schema::dropIfExists('xactimate_report_types');
    }
};
