<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('123123'),
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Admin2',
            'email' => 'admin2@admin.com',
            'password' => bcrypt('123123'),
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => bcrypt('123123'),
            'role' => 'user',
        ]);
        User::create([
            'name' => 'User2',
            'email' => 'user2@user.com',
            'password' => bcrypt('123123'),
            'role' => 'user',
        ]);
    }
}
