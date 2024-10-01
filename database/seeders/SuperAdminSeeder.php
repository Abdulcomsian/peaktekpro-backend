<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User;
        $user->role_id = 7;
        $user->company_id = 0;
        $user->name = 'Super Admin';
        $user->email = 'superAdmin@gmail.com';
        $user->password = Hash::make('Abc@123!');
        $user->created_by = 0;
        $user->status = "active";
        $user->save();
    }
}
