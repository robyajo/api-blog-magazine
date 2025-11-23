<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Rfc4122\UuidV4;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'uuid' => UuidV4::uuid4()->toString(),
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('string'),
            'role' => 'admin',
        ]);
        User::create([
            'uuid' => UuidV4::uuid4()->toString(),
            'name' => 'Admin2',
            'email' => 'admin2@admin.com',
            'password' => bcrypt('string'),
            'role' => 'admin',
        ]);
        User::create([
            'uuid' => UuidV4::uuid4()->toString(),
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => bcrypt('string'),
            'role' => 'user',
        ]);
        User::create([
            'uuid' => UuidV4::uuid4()->toString(),
            'name' => 'User2',
            'email' => 'user2@user.com',
            'password' => bcrypt('string'),
            'role' => 'user',
        ]);
    }
}
