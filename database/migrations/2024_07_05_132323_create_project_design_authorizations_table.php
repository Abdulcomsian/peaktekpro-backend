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
        Schema::create('project_design_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')->constrained('company_jobs')->onDelete('cascade');
            $table->text('disclaimer')->nullable();
            $table->text('signer_first_name')->nullable();
            $table->text('signer_last_name')->nullable();
            $table->text('signer_email')->unique()->nullable();
            $table->text('footer_notes')->nullable();
            $table->text('item1')->nullable();
            $table->text('item2')->nullable();
            $table->text('item3')->nullable();
            $table->text('section1')->nullable();
            $table->text('section2')->nullable();
            $table->text('section3')->nullable();
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
        Schema::dropIfExists('project_design_authorizations');
    }
};
