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
        Schema::create('job_log_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_log_id')->constrained('job_logs')->onDelete('cascade');
            $table->string('item');
            $table->text('description')->nullable();
            $table->string('cost');
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
        Schema::dropIfExists('job_log_items');
    }
};
