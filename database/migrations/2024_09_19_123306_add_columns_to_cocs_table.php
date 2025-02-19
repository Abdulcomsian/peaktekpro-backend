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
        Schema::table('cocs', function (Blueprint $table) {
            $table->string('conclusion')->nullable()->after('released_to');
            $table->string('sincerely')->nullable()->after('conclusion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cocs', function (Blueprint $table) {
            $table->dropColumn('conclusion');
            $table->dropColumn('sincerely');
        });
    }
};
