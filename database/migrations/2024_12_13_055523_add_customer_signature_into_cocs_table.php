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
            $table->longText('customer_signature')->after('notes')->nullable();
            $table->longText('company_representative_signature')->after('company_representative')->nullable();
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
            //
        });
    }
};
