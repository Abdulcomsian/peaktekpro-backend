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
        Schema::table('adjustor_square_photos', function (Blueprint $table) {
            $table->dropForeign(['company_job_id']);
            $table->dropColumn('company_job_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adjustor_square_photos', function (Blueprint $table) {
            $table->unsignedBigInteger('company_job_id')->nullable();

            $table->foreign('company_job_id')->references('id')->on('company_jobs')->onDelete('cascade');

        });
    }
};
