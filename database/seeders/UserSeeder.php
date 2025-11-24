<?php

namespace Database\Seeders;

use App\Models\User;
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
            'email_verified_at' => now(),
            'avatar_url' => 'https://plus.unsplash.com/premium_vector-1719858611039-66c134efa74d?q=80&w=880&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'password' => bcrypt('string'),
            'role' => 'admin',
        ]);
        User::create([
            'uuid' => UuidV4::uuid4()->toString(),
            'name' => 'Admin2',
            'email' => 'admin2@admin.com',
            'email_verified_at' => now(),
            'avatar_url' => 'https://plus.unsplash.com/premium_vector-1719858611039-66c134efa74d?q=80&w=880&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'password' => bcrypt('string'),
            'role' => 'admin',
        ]);
        User::create([
            'uuid' => UuidV4::uuid4()->toString(),
            'name' => 'User',
            'email' => 'user@user.com',
            'email_verified_at' => now(),
            'avatar_url' => 'https://plus.unsplash.com/premium_vector-1719858612540-b8abdec0ec17?q=80&w=880&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'password' => bcrypt('string'),
            'role' => 'user',
        ]);
        User::create([
            'uuid' => UuidV4::uuid4()->toString(),
            'name' => 'User2',
            'email' => 'user2@user.com',
            'email_verified_at' => now(),
            'avatar_url' => 'https://plus.unsplash.com/premium_vector-1719858612540-b8abdec0ec17?q=80&w=880&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'password' => bcrypt('string'),
            'role' => 'user',
        ]);
    }
}
