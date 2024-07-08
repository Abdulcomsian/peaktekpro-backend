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
        Schema::create('authorization_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authorization_id')->constrained('project_design_authorizations')->onDelete('cascade');
            $table->string('title');
            $table->string('section_total')->nullable();
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
        Schema::dropIfExists('authorization_sections');
    }
};
