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
        Schema::table('final_payment_dues', function (Blueprint $table) {
            $table->string('current_stage',50)->default('no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('final_payment_dues', function (Blueprint $table) {
            $table->dropColumn('current_stage');
        });
    }
};
