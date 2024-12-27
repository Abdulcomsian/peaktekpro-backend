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
            $table->string('status')->nullable()->default(null)->change(); // Modify column directly

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
            $table->string('status')->default('false')->change(); // Restore original column definition

        });
    }
};
