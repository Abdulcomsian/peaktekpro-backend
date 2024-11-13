<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pages = [
            [
                'name' => 'Introduction',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Repairability Assessment',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Repairability or Compatibility Photos',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Product Compatibility',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Unfair Claims Practices',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Applicable Codes & Guidelines',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Quote Details',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Authorization Page',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Terms and Conditions',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Warranty',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        Page::insert($pages);

    }
}
