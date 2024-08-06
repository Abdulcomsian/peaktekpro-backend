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
        Schema::create('overturn_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('time')->nullable();
            $table->string('date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
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
        Schema::dropIfExists('overturn_meetings');
    }
};
