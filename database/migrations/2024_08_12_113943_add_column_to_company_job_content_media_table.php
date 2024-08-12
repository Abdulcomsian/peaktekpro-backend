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
        Schema::table('company_job_content_media', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('media_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_job_content_media', function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
};
