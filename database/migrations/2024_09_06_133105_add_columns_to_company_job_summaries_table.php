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
            $table->string('insurance')->nullable()->after('lead_source');
            $table->string('policy_number')->nullable()->after('insurance');
            $table->string('email')->nullable()->after('policy_number');
            $table->string('insurance_representative')->nullable()->after('email');
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
            $table->dropColumn('insurance');
            $table->dropColumn('policy_number');
            $table->dropColumn('email');
            $table->dropColumn('insurance_representative');
        });
    }
};
