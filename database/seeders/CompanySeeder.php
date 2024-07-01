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
        $company = new Company;
        $company->name = 'Peak Tek';
        $company->save();

        $user = new User;
        $user->role_id = 1;
        $user->name = 'Peak Tek';
        $user->email = 'peaktek@gmail.com';
        $user->password = Hash::make('Abc@123!');
        $user->save();

        $user_role = new UserRole;
        $user_role->company_id = $company->id;
        $user_role->user_id = $user->id;
        $user_role->save();
    }
}
