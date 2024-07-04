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
        Schema::create('project_design_page_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->foreignId('project_design_page_id')->constrained('project_design_pages')->onDelete('cascade');
            $table->boolean('status')->default(0); //a for on 0 for off
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
        Schema::dropIfExists('project_design_page_statuses');
    }
};
