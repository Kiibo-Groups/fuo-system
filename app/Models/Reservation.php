<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = ['generator_id', 'branch_id', 'client_name', 'client_phone', 'expires_at', 'is_active'];
    protected $casts = ['expires_at' => 'datetime'];

    public function generator()
    {
        return $this->belongsTo(Generator::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
