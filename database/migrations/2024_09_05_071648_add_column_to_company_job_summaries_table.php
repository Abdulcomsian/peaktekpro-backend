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
            $table->string('invoice_number')->nullable()->after('balance');
            $table->string('market')->nullable()->after('invoice_number');
            $table->string('lead_source')->nullable()->after('market');
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
            $table->dropColumn('invoice_number');
            $table->dropColumn('market');
            $table->dropColumn('lead_source');
        });
    }
};
