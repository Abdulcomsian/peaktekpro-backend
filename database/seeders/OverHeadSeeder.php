<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OverheadPercentage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OverHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $overhead = new OverheadPercentage;
        $overhead->overhead_percentage = 15;
        $overhead->save();
    }
}
