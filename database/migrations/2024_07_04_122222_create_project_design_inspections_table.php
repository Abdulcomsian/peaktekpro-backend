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
        Schema::create('project_design_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->text('inspection');
            $table->string('inspection_index');
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
        Schema::dropIfExists('project_design_inspections');
    }
};
