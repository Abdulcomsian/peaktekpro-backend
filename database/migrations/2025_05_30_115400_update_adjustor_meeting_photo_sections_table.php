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
        Schema::table('adjustor_meeting_photo_sections', function (Blueprint $table) {
             // Drop foreign key and column
            $table->dropForeign(['adjustor_meeting_id']);
            $table->dropColumn('adjustor_meeting_id');

            // Add new foreign key to company_jobs
            $table->foreignId('company_job_id')
                  ->nullable()
                  ->constrained('company_jobs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adjustor_meeting_photo_sections', function (Blueprint $table) {
            
            $table->dropForeign(['company_job_id']);
            $table->dropColumn('company_job_id');

            // Restore the old foreign key
            $table->foreignId('adjustor_meeting_id')
                  ->constrained('adjustor_meetings');

        });
    }
};
