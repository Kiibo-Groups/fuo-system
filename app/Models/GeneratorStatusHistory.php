<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorStatusHistory extends Model
{
    protected $table = 'generator_status_history';
    protected $fillable = ['generator_id', 'user_id', 'previous_status', 'new_status', 'comment'];
    public function user() { return $this->belongsTo(User::class); }
}
