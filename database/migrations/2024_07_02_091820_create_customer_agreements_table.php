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
        Schema::create('customer_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('insurance')->nullable();
            $table->string('claim_number')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('sign_image_url')->nullable();
            $table->string('sign_pdf_url')->nullable();
            $table->string('company_signature')->nullable();
            $table->string('company_printed_name')->nullable();
            $table->string('company_date')->nullable();
            $table->string('customer_signature')->nullable();
            $table->string('customer_printed_name')->nullable();
            $table->string('customer_date')->nullable();
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
        Schema::dropIfExists('customer_agreements');
    }
};
