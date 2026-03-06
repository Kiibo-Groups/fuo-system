<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    protected $fillable = ['title', 'items', 'is_active'];
    protected $casts = ['items' => 'array'];
}
