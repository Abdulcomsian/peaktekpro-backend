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
        Schema::create('job_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->string('rep1_name');
            $table->string('rep1_email')->unique();
            $table->string('rep1_phone');
            $table->string('rep2_name');
            $table->string('rep2_email')->unique();
            $table->string('rep2_phone');
            $table->string('customer_name');
            $table->string('job_total');
            $table->string('overhead_total');
            $table->string('purchase_order_number');
            $table->string('total_profit');
            $table->string('commission_rep1');
            $table->string('commission_rep2');
            $table->string('team_lead');
            $table->string('net_to_company');
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
        Schema::dropIfExists('job_logs');
    }
};
