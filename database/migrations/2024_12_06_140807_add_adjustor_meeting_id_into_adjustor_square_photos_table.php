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
        Schema::table('adjustor_square_photos', function (Blueprint $table) {
            $table->unsignedBigInteger('adjustor_meeting_id')->nullable()->after('id');

            // Add the foreign key constraint
            $table->foreign('adjustor_meeting_id')
                ->references('id')
                ->on('adjustor_meetings')
                ->onDelete('cascade');        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adjustor_square_photos', function (Blueprint $table) {
            $table->dropForeign(['adjustor_meeting_id']);
            $table->dropColumn('adjustor_meeting_id');

        });
    }
};
