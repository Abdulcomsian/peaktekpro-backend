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
        Schema::table('insurance_under_reviews', function (Blueprint $table) {
            $table->string('pdf_path')->after('status')->nullable();
            $table->string('file_name')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('insurance_under_reviews', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
            $table->dropColumn('file_name');

        });
    }
};
