<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companyLocation = new Location;
        $companyLocation->name = 'Nashville';
        $companyLocation->created_by = 0;
        $companyLocation->save();

        $companyLocation = new Location;
        $companyLocation->name = 'Chattanooga';
        $companyLocation->created_by = 0;
        $companyLocation->save();

        $companyLocation = new Location;
        $companyLocation->name = 'Knoxville';
        $companyLocation->created_by = 0;
        $companyLocation->save();
    }
}
