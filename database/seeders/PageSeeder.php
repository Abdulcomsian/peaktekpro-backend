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
                'slug' => 'introduction',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Repairability Assessment',
                'slug' => 'repairability-assessment',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Repairability or Compatibility Photos',
                'slug' => 'repairability-or-compatibility-photos',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Product Compatibility',
                'slug' => 'product-compatibility',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Unfair Claims Practices',
                'slug' => 'unfair-claims-practices',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Applicable Codes & Guidelines',
                'slug' => 'applicable-codes-guidelines',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Quote Details',
                'slug' => 'quote-details',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Authorization Page',
                'slug' => 'authorization-page',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Terms and Conditions',
                'slug' => 'terms-and-conditions',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Warranty',
                'slug' => 'warranty',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        Page::insert($pages);

    }
}
