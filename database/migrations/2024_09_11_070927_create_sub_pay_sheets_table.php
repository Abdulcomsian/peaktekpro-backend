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
        Schema::create('sub_pay_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_complete_id')->constrained('build_completes')->onDelete('cascade');
            $table->string('contractor')->nullable();
            $table->string('contractor_email')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('sub_pay_sheets');
    }
};
