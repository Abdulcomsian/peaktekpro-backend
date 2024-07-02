<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['name' => 'New Lead'],
            ['name' => 'Signed Deal'],
            ['name' => 'Adjustor'],
            ['name' => 'Full Approval & Overturn'],
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
