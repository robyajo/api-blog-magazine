<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::create([
            'id' => 1,
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user_id' => 1,
            'categori_id' => 1,
            'name' => 'Bài viết 1',
            'slug' => 'bai-viet-1',
            'image' => 'image.jpg',
            'image_url' => 'https://example.com/image.jpg',
            'status' => 'published',
            'views' => 100,
            'likes' => 50,
            'dislikes' => 10,
            'comments' => 20,
            'shares' => 15,
            'favorites' => 10,
            'tags' => 'tag1,tag2',
            'content' => 'Nội dung bài viết 1',
            'description' => 'Mô tả bài viết 1',
        ]);

        Post::create([
            'id' => 2,
            'uuid' => '123e4567-e89b-12d3-a456-426614174001',
            'user_id' => 1,
            'categori_id' => 1,
            'name' => 'Bài viết 2',
            'slug' => 'bai-viet-2',
            'image' => 'image.jpg',
            'image_url' => 'https://example.com/image.jpg',
            'status' => 'published',
            'views' => 100,
            'likes' => 50,
            'dislikes' => 10,
            'comments' => 20,
            'shares' => 15,
            'favorites' => 10,
            'tags' => 'tag1,tag2',
            'content' => 'Nội dung bài viết 2',
            'description' => 'Mô tả bài viết 2',
        ]);
        Post::create([
            'id' => 3,
            'uuid' => '123e4567-e89b-12d3-a456-426614174002',
            'user_id' => 1,
            'categori_id' => 2,
            'name' => 'Bài viết 3',
            'slug' => 'bai-viet-3',
            'image' => 'image.jpg',
            'image_url' => 'https://example.com/image.jpg',
            'status' => 'published',
            'views' => 100,
            'likes' => 50,
            'dislikes' => 10,
            'comments' => 20,
            'shares' => 15,
            'favorites' => 10,
            'tags' => 'tag1,tag2',
            'content' => 'Nội dung bài viết 3',
            'description' => 'Mô tả bài viết 3',
        ]);
    }
}
