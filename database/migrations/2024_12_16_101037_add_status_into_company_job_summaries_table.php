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
            $table->string('is_fully_paid')->after('balance')->default('no');
            $table->date('full_payment_date')->after('is_fully_paid')->nullable();
            $table->string('overhead_percentage')->after('full_payment_date')->default('15');
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
            $table->dropColumn('is_fully_paid');
            $table->dropColumn('full_payment_date');
            $table->dropColumn('overhead_percentage');

        });
    }
};
