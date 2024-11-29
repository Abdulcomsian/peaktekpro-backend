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
        Schema::create('adjustor_meeting_photo_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustor_meeting_id')->constrained('adjustor_meetings');
            $table->string('front')->nullable();
            $table->string('exteriorPhotos_front')->nullable();
            $table->string('front_left')->nullable();
            $table->string('exteriorPhotos_front_left')->nullable();
            $table->string('left')->nullable();
            $table->string('exteriorPhotos_left')->nullable();
            $table->string('back_left')->nullable();
            $table->string('exteriorPhotos_back_left')->nullable();
            $table->string('back')->nullable();
            $table->string('exteriorPhotos_back')->nullable();
            $table->string('back_right')->nullable();
            $table->string('exteriorPhotos_back_right')->nullable();
            $table->string('right')->nullable();
            $table->string('exteriorPhotos_right')->nullable();
            $table->string('front_right')->nullable();
            $table->string('exteriorPhotos_front_right')->nullable();
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
        Schema::dropIfExists('adjustor_meeting_photo_sections');
    }
};
