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
        Schema::table('project_design_titles', function (Blueprint $table) {
            $table->string('primary_image_file_name')->nullable()->after('primary_image');
            $table->string('secondary_image_file_name')->nullable()->after('secondary_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_design_titles', function (Blueprint $table) {
            $table->dropColumn('primary_image_file_name');
            $table->dropColumn('secondary_image_file_name');
        });
    }
};
