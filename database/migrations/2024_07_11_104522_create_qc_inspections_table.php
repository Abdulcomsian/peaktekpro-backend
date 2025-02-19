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
        Schema::create('qc_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('street');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('insurance');
            $table->string('claim_number')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('company_representative')->nullable();
            $table->string('company_printed_name')->nullable();
            $table->string('company_signed_date')->nullable();
            $table->string('customer_signature')->nullable();
            $table->string('customer_printed_name')->nullable();
            $table->string('customer_signed_date')->nullable();
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
        Schema::dropIfExists('qc_inspections');
    }
};
