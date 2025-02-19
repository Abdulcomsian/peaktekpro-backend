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
            DB::table('insurance_under_reviews')
                ->where('status','denied')
                ->update(['status'=>'overturn']);

                DB::statement("ALTER TABLE insurance_under_reviews MODIFY COLUMN status ENUM('approved', 'overturn') NULL;");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            DB::table('insurance_under_reviews')
            ->where('status', 'overturn')
            ->update(['status' => 'denied']);   

            DB::statement("ALTER TABLE insurance_under_reviews MODIFY COLUMN status ENUM('approved', 'denied') NULL;");

    }
};
