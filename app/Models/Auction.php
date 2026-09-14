<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{

    protected $fillable = [
        'generator_id',
        'branch_id',
        'start_price',
        'current_price',
        'min_increment',
        'start_time',
        'end_time',
        'payment_deadline',
        'status',
        'winner_user_id'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'payment_deadline' => 'datetime',
    ];

    public function generator()
    {
        return $this->belongsTo(Generator::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function assets()
    {
        return $this->hasMany(AuctionAsset::class)->orderBy('order', 'asc');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope a query to only include auctions visible to a specific user based on branch.
     */
    public function scopeVisibleForUser($query, $user)
    {
        // Si el usuario es GLOBAL (branch_id null), ve todas las subastas.
        if (is_null($user->branch_id)) {
            return $query;
        }

        // Si el usuario tiene una sucursal, ve las de su sucursal + las globales (branch_id null).
        return $query->where(function($q) use ($user) {
            $q->where('branch_id', $user->branch_id)
              ->orWhereNull('branch_id');
        });
    }
}
