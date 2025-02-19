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
        Schema::create('insurance_under_review_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_under_reviews_id')->constrained('insurance_under_reviews')->onDelete('cascade');
            $table->string('photo')->nullable();
            $table->string('label')->nullable();
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
        Schema::dropIfExists('insurance_under_review_photos');
    }
};
