<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adjustor_square_photos', function (Blueprint $table) {
            // $table->unsignedBigInteger('adjustor_meeting_id')->nullable()->after('id');

            // // Add the foreign key constraint
            // $table->foreign('adjustor_meeting_id')
            //     ->references('id')
            //     ->on('adjustor_meetings')
            //     ->onDelete('cascade');        
            if (!DB::getSchemaBuilder()->hasColumn('adjustor_square_photos', 'adjustor_meeting_id')) {
                $table->unsignedBigInteger('adjustor_meeting_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adjustor_square_photos', function (Blueprint $table) {
            // $table->dropForeign(['adjustor_meeting_id']);
            // $table->dropColumn('adjustor_meeting_id');
            if (DB::getSchemaBuilder()->hasColumn('adjustor_square_photos', 'adjustor_meeting_id')) {
                $table->dropColumn('adjustor_meeting_id');
            }
        });
    }
};
