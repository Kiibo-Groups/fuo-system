<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionAsset extends Model
{
    protected $fillable = [
        'auction_id',
        'file_path',
        'type',
        'order'
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}
