<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Model;

class UploadedPhoto extends Model
{
    public $table = 'blog_etc_uploaded_photos';

    public $casts = [
        'uploaded_images' => 'array',
    ];

    public $fillable = [
        'image_title',
        'uploader_id',
        'source',
        'uploaded_images',
    ];
}
