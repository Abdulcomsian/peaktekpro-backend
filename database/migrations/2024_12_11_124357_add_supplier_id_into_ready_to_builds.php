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
        Schema::table('ready_to_builds', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->nullable() // Mark the column as nullable first
                ->constrained('users') // Define the table reference
                ->onDelete('set null'); // Handle deletions (optional but recommended)
            $table->string('supplier_name')->nullable();
            $table->string('supplier_email')->nullable();
        });
    }

    public function down()
    {
        Schema::table('ready_to_builds', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']); // Drop foreign key
            $table->dropColumn('supplier_id');    // Drop column
            $table->dropColumn('supplier_name');
            $table->dropColumn('supplier_email');
        });
    }

};
