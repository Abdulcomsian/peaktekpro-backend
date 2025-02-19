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
        Schema::table('material_orders', function (Blueprint $table) {
            $table->integer('supplier_id')->nullable()->after('company_job_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_orders', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
        });
    }
};
