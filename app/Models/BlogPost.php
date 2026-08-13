<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'image_path',
        'image_disk',
        'meta_title',
        'meta_description',
        'category',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }
}
