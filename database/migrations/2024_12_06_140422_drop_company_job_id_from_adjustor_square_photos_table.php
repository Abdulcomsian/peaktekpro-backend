<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            // $table->dropForeign(['company_job_id']);
            // $table->dropColumn('company_job_id');
            if (DB::getSchemaBuilder()->hasColumn('adjustor_square_photos', 'company_job_id')) {
                $table->dropForeign(['company_job_id']); // Drop foreign key
                $table->dropColumn('company_job_id');    // Drop column
            }

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
            $table->foreignId('company_job_id')
            ->nullable()
            ->constrained('company_jobs')
            ->onDelete('cascade');
        });
    }
};
