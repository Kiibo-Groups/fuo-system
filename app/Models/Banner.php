<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'target_url',
        'target_audience',
        'is_active',
        'order'
    ];
}
