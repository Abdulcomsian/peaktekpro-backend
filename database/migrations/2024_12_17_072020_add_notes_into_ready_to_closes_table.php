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
        Schema::table('ready_to_closes', function (Blueprint $table) {
            $table->string('net_revenue')->after('additional_costs')->nullable();
            $table->string('net_profit')->after('net_revenue')->nullable();
            $table->longText('notes')->after('net_profit')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ready_to_closes', function (Blueprint $table) {
            $table->dropColumn('net_revenue');
            $table->dropColumn('net_profit');
            $table->dropColumn('notes');

        });
    }
};
