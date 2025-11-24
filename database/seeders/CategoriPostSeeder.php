<?php

namespace Database\Seeders;

use App\Models\CategoriPost;
use Illuminate\Database\Seeder;

class CategoriPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        CategoriPost::create([
            'id' => 1,
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'name' => 'Category 1',
            'user_id' => 1,
            'slug' => 'category-1',
            'status' => 'active',
        ]);
        CategoriPost::create([
            'id' => 2,
            'uuid' => '123e4567-e89b-12d3-a456-426614174001',
            'name' => 'Category 2',
            'user_id' => 1,
            'slug' => 'category-2',
            'status' => 'active',
        ]);
    }
}
