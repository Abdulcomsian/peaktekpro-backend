<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['name' => 'Company'],
            ['name' => 'Site Admin'], //Manager
            ['name' => 'Sub Contractor'], //Like Contractor
            ['name' => 'Supplier'], //Like Material Company Contract
            ['name' => 'User'],  //Like Employee
            ['name' => 'Adjustor'],
            ['name' => 'Super Admin'],
        ];

        foreach($roles as $role)
        {
            Role::create($role);
        }
    }
}
