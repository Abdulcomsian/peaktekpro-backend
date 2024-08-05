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
        Schema::create('company_job_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->string('job_total')->nullable();
            $table->string('first_payment')->nullable();
            $table->string('first_payment_cheque_number')->nullable();
            $table->string('deductable')->nullable();
            $table->string('deductable_cheque_number')->nullable();
            $table->string('upgrades')->nullable();
            $table->string('upgrades_cheque_number')->nullable();
            $table->string('final_payment')->nullable();
            $table->string('final_payment_cheque_number')->nullable();
            $table->string('balance')->nullable();
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
        Schema::dropIfExists('company_job_summaries');
    }
};
