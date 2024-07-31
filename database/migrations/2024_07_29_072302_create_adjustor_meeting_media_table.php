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
        Schema::create('adjustor_meeting_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustor_id')->constrained('adjustor_meetings')->onDelete('cascade');
            $table->string('media_type');
            $table->string('media_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adjustor_meeting_media');
    }
};
