<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'id',
        'uuid',
        'user_id',
        'categori_id',
        'name',
        'slug',
        'image',
        'image_url',
        'status',
        'views',
        'likes',
        'dislikes',
        'comments',
        'shares',
        'favorites',
        'tags',
        'content',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categori()
    {
        return $this->belongsTo(CategoriPost::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) str()->uuid();
            }
        });
    }
}
