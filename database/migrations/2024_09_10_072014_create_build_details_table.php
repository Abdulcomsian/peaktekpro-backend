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
        Schema::create('build_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->string('build_date')->nullable();
            $table->string('build_time')->nullable();
            $table->string('homeowner')->nullable();
            $table->string('homeowner_email')->nullable();
            $table->string('contractor')->nullable();
            $table->string('contractor_email')->nullable();
            $table->string('supplier')->nullable();
            $table->string('supplier_email')->nullable();
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
        Schema::dropIfExists('build_details');
    }
};
