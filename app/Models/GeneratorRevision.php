<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorRevision extends Model
{
    protected $fillable = ['generator_id', 'technician_id', 'checklist_results', 'observations', 'result'];
    protected $casts = ['checklist_results' => 'array'];
    public function generator() { return $this->belongsTo(Generator::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
}
