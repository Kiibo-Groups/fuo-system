<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorImage extends Model
{
    protected $fillable = [
        'internal_folio',
        'file_path',
        'original_name',
        'uploaded_by',
        'generator_id',
        'matched',
    ];

    protected $casts = [
        'matched' => 'boolean',
    ];

    public function generator()
    {
        return $this->belongsTo(Generator::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Obtiene la URL pública de la imagen */
    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::url($this->file_path);
    }
}
