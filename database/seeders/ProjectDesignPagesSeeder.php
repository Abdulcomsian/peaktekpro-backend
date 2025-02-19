<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectDesignPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProjectDesignPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pages = [
            ['name' => 'Title'],
            ['name' => 'Introduction'],
            ['name' => 'Inspection'],
            ['name' => 'Quote Details'],
            ['name' => 'Authorization'],
            ['name' => 'Payment Schedule'],
            ['name' => 'Roof Components Generic'],
            ['name' => 'Xactimate Report From Insurance'],
            ['name' => 'Terms And Conditions'],
        ];

        foreach($pages as $page)
        {
            ProjectDesignPage::create($page);
        }
    }
}
