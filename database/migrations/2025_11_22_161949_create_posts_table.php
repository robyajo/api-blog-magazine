<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('categori_id')->constrained('categori_posts')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->string('views')->default(0);
            $table->string('likes')->default(0);
            $table->string('dislikes')->default(0);
            $table->string('comments')->default(0);
            $table->string('shares')->default(0);
            $table->string('favorites')->default(0);
            $table->string('tags')->nullable();
            $table->text('content')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
