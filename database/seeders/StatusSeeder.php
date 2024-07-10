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
            ['name' => 'New Lead'],
            ['name' => 'Signed Deal'],
            ['name' => 'Adjustor'],
            ['name' => 'Full Approval'],
            ['name' => 'Overturn'],
            ['name' => 'Appraisal'],
            ['name' => 'Approved'],
            ['name' => 'Design Meeting'],
            ['name' => 'Schedule'],
            ['name' => 'Ready To Built'],
            ['name' => 'In Progress'],
            ['name' => 'COC'],
            ['name' => 'Completed'],
        ];

        foreach($statuses as $status)
        {
            Status::create($status);
        }
    }
}
