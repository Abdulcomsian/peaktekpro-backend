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
        Schema::create('cocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('street');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('insurance')->nullable();
            $table->string('claim_number')->nullable();
            $table->boolean('status')->default(false);
            $table->string('policy_number')->nullable();
            $table->string('company_representative')->nullable();
            $table->string('company_printed_name')->nullable();
            $table->string('company_signed_date')->nullable();
            $table->string('job_total')->nullable();
            $table->string('customer_paid_upgrades')->nullable();
            $table->string('deductible')->nullable();
            $table->string('acv_check')->nullable();
            $table->string('rcv_check')->nullable();
            $table->text('supplemental_items')->nullable();
            $table->string('awarded_to')->nullable();
            $table->string('released_to')->nullable();
            $table->boolean('coc_insurance_email_sent')->default(false);
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
        Schema::dropIfExists('cocs');
    }
};
