<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Comment::create([
            'id' => 1,
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user_id' => 1,
            'post_id' => 1,
            'name' => 'Nguyễn Văn A',
            'email' => 'nguyenvana@example.com',
            'phone' => '0987654321',
            'media' => 'https://example.com/avatar.jpg',
            'content' => 'Nội dung bình luận 1',
        ]);
        Comment::create([
            'id' => 2,
            'uuid' => '123e4567-e89b-12d3-a456-426614174001',
            'user_id' => 1,
            'post_id' => 1,
            'name' => 'Nguyễn Văn B',
            'email' => 'nguyenvana@example.com',
            'phone' => '0987654321',
            'media' => 'https://example.com/avatar.jpg',
            'content' => 'Nội dung bình luận 2',
        ]);
    }
}
