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
        Schema::table('claim_details', function (Blueprint $table) {
            $table->string('insurance_company')->nullable();
            $table->string('desk_adjustor')->nullable();
            $table->string('email')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_details', function (Blueprint $table) {
            $table->dropColumn('insurance_company');
            $table->dropColumn('desk_adjustor');
            $table->dropColumn('email');

        });
    }
};
