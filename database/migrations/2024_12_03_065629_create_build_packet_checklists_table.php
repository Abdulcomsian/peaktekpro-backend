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
        Schema::create('build_packet_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs');
            $table->string('project_overview')->default('false');
            $table->string('scope_of_work')->default('false');
            $table->string('customer_preparation')->default('false');
            $table->string('photo_documentation')->default('false');
            $table->string('product_selection')->default('false');
            $table->string('authorization')->default('false');
            $table->string('terms_condition')->default('false');
            $table->string('is_complete')->default('false');
            $table->string('status')->default('approved');
            $table->string('sign_image_url')->nullable();
            $table->string('pdf_url')->nullable();


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
        Schema::dropIfExists('build_packet_checklists');
    }
};
