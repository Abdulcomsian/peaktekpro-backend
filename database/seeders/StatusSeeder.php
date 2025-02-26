<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Truncate the table
        Status::truncate();
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $statuses = [
            ['name' => 'New Leads'],
            ['name' => 'Customer Agreement'],
            ['name' => 'Estimate Prepared'],
            ['name' => 'Adjuster Scheduled'],
            ['name' => 'Ins Under Review'],
            ['name' => 'Overturn'],
            ['name' => 'Appraisal'],
            ['name' => 'Approved'],
            ['name' => 'Crew Information'],
            ['name' => 'Ready To Build'],
            ['name' => 'Build Scheduled'],
            ['name' => 'In Progress'],
            ['name' => 'Build Complete'],
            ['name' => 'COC Required'],
            ['name' => 'Final Payment Due'],
            ['name' => 'Ready to Close'],
            ['name' => 'Supplement Submitted'],
            ['name' => 'Won and Closed'],
            ['name' => 'Lost'],
            ['name' => 'Unqualified'],
            ['name' => 'Completed'],
            


        ];

        foreach($statuses as $status)
        {
            Status::create($status);
        }
    }
}
