<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['material' => '7/16” OSB ', 'orderkey' => 'Sheet (32 SQ FT)'],
            ['material' => 'Plastic Cap Nails', 'orderkey' => 'Box (25 SQ)'],
            ['material' => '3/8” Staples', 'orderkey' => 'Box (10 SQ)'],
            ['material' => '1-1/4” Nails' , 'orderkey' => 'Box (15 SQ)'],
            ['material' => 'ice & water' , 'orderkey' => 'Roll (66 LFT/2 SQ)'],
            ['material' => 'proArmor' , 'orderkey' => 'Roll (10 SQ)'],
            ['material' => 'Starter Strip' , 'orderkey' => 'Bundle (100 LFT)'],
            ['material' => 'Drip Edge' , 'orderkey' => 'Stick (10 LFT)'],
            // ['material' => '' , 'orderkey' => ''],
            // ['material' => '' , 'orderkey' => ''],
            // ['material' => '' , 'orderkey' => ''],
            // ['material' => '' , 'orderkey' => ''],
            // ['material' => '' , 'orderkey' => ''],
            // ['material' => '' , 'orderkey' => ''],
            // ['material' => '' , 'orderkey' => ''],

        ];

        DB::table('materials')->insert($data);

          // Sub-options
          $subOptions = [
            ['material_id' => 5, 'name' => 'Ice and Water Barrier'],
            ['material_id' => 5, 'name' => 'Rhlno G'],
            ['material_id' => 5, 'name' => 'Weatherlock G'],
            ['material_id' => 6, 'name' => 'Underlayment'],
            ['material_id' => 6, 'name' => 'proArmer'],
            ['material_id' => 6, 'name' => 'Rhino U20'],

        ];

        DB::table('sub_options')->insert($subOptions);

    }
}
