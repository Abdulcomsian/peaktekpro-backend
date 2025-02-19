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
        Schema::table('company_job_summaries', function (Blueprint $table) {
            $table->string('claim_number')->nullable()->after('insurance_representative');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_job_summaries', function (Blueprint $table) {
            $table->dropColumn('claim_number');
        });
    }
};
