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
        Schema::create('ready_to_closes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->string('sales_rep1_commission_percentage')->nullable();
            $table->string('sales_rep2_commission_percentage')->nullable();
            $table->string('deal_value')->nullable();
            $table->string('material_costs')->nullable();
            $table->string('labor_costs')->nullable();
            $table->string('costs_of_goods')->nullable();
            $table->string('market')->nullable();
            $table->string('status')->default('false');
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
        Schema::dropIfExists('ready_to_closes');
    }
};
