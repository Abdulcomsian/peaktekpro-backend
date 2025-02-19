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
        Schema::table('ready_to_builds', function (Blueprint $table) {
            $table->integer('sub_contractor_id')->nullable()->after('company_job_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ready_to_builds', function (Blueprint $table) {
            $table->dropColumn('sub_contractor_id');
        });
    }
};
