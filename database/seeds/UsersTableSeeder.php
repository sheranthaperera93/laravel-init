<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        User::truncate();
        
        // Adding default system users
        User::create([
            'name' => 'Super Admin',
            'email' => 'super_admin@globalwavenet.com',
            'password' => bcrypt('SuperAdmin@123'),
            'role_code' =>  1
        ]);
    }

    
}
