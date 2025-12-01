<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role_id' => 1, // admin
                'department_id' => 1,
            ],
            [
                'name' => 'Super User',
                'email' => 'super@example.com',
                'password' => Hash::make('super123'),
                'role_id' => 2, // superuser
                'department_id' => 1,
            ],
            [
                'name' => 'Regular User',
                'email' => 'user1@example.com',
                'password' => Hash::make('user123'),
                'role_id' => 3, // user
                'department_id' => 1,
            ],
        ]);
    }
}
