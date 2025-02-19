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
        Schema::create('material_orders', function (Blueprint $table) {
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
            $table->string('date_needed')->nullable();
            $table->string('square_count')->nullable();
            $table->string('total_perimeter')->nullable();
            $table->string('ridge_lf')->nullable();
            $table->string('build_date')->nullable();
            $table->string('valley_sf')->nullable();
            $table->string('hip_and_ridge_lf')->nullable();
            $table->string('drip_edge_lf')->nullable();
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
        Schema::dropIfExists('material_orders');
    }
};
