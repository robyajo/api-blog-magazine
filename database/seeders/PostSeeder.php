<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        DB::connection()->disableQueryLog();
        Schema::disableForeignKeyConstraints();
        DB::table('posts')->truncate();
        Schema::enableForeignKeyConstraints();

        $img1 = 'https://images.unsplash.com/photo-1495020689067-958852a7765e?q=80&w=1169&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
        $img2 = 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
        $total = 1000000;
        $batchSize = 500;
        $batch = [];

        for ($i = 1; $i <= $total; $i++) {
            $name = 'Generated Post '.$i;
            $batch[] = [
                'uuid' => (string) Str::uuid(),
                'user_id' => 1,
                'categori_id' => 2,
                'name' => $name,
                'slug' => Str::slug($name),
                'image' => null,
                'image_url' => $i % 2 === 0 ? $img1 : $img2,
                'status' => 'published',
                'views' => 0,
                'likes' => 0,
                'dislikes' => 0,
                'comments' => 0,
                'shares' => 0,
                'favorites' => 0,
                'tags' => 'tag1,tag2',
                'content' => 'Generated content for '.$name,
                'description' => 'Generated description for '.$name,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) === $batchSize) {
                DB::table('posts')->insert($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('posts')->insert($batch);
        }
    }
}
