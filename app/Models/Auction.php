<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{

    protected $fillable = [
        'generator_id',
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
}
