<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Runner\AfterTestHook;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inprogresses', function (Blueprint $table) {
            $table->mediumText('production_sign_url')->after('status')->nullable();
            $table->mediumText('homeowner_signature')->after('production_sign_url')->nullable();
            $table->mediumText('pdf_url')->after('homeowner_signature')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inprogresses', function (Blueprint $table) {
            $table->dropColumn('production_sign_url');
            $table->dropColumn('homeowner_signature');
            $table->dropColumn('pdf_url');
        });
    }
};
