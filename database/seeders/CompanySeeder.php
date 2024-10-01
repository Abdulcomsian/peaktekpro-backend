<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Company
        $company = new Company;
        $company->name = 'Peak Tek';
        $company->save();

        $user = new User;
        $user->role_id = 1;
        $user->company_id = $company->id;
        $user->name = 'Peak Tek Admin';
        $user->email = 'peaktek@gmail.com';
        $user->password = Hash::make('Abc@123!');
        $user->created_by = 1;
        $user->status = "active";
        $user->save();

        $user_role = new UserRole;
        $user_role->company_id = $company->id;
        $user_role->user_id = $user->id;
        $user_role->save();

        //Company Created

        // Create Manager
        $manager = new User;
        $manager->role_id = 2;
        $manager->company_id = $company->id;
        $manager->name = 'Peak Tek Manager';
        $manager->email = 'peaktekmanager@gmail.com';
        $manager->password = Hash::make('Abc@123!');
        $manager->created_by = 2;
        $manager->status = "active";
        $manager->save();

        $user_role = new UserRole;
        $user_role->company_id = $company->id;
        $user_role->user_id = $manager->id;
        $user_role->save();

        //Manager Created
    }
}
