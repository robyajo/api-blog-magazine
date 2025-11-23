<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categori_posts';
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
