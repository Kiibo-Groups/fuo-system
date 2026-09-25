<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionRegistration extends Model
{
    protected $fillable = [
        'auction_id',
        'user_id',
        'status',
        'payment_id',
        'hold_amount',
        'max_bid'
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
