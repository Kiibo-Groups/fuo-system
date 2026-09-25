<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessProxyBids implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $auctionId;

    public function __construct($auctionId)
    {
        $this->auctionId = $auctionId;
    }

    public function handle(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $auction = \App\Models\Auction::where('id', $this->auctionId)->lockForUpdate()->first();
            
            if (!$auction || $auction->status !== 'active') return;

            $currentWinnerId = \App\Models\Bid::where('auction_id', $auction->id)
                ->orderBy('id', 'desc')->first()->user_id ?? null;

            if (!$currentWinnerId) return;

            $highestProxy = \App\Models\AuctionRegistration::where('auction_id', $auction->id)
                ->where('user_id', '!=', $currentWinnerId)
                ->whereNotNull('max_bid')
                ->orderBy('max_bid', 'desc')
                ->first();

            if ($highestProxy && $highestProxy->max_bid >= ($auction->current_price + $auction->min_increment)) {
                $autoBidAmount = $auction->current_price + $auction->min_increment;
                
                if ($autoBidAmount <= $highestProxy->max_bid) {
                    $autoBid = \App\Models\Bid::create([
                        'auction_id' => $auction->id,
                        'user_id' => $highestProxy->user_id,
                        'amount' => $autoBidAmount,
                        'is_auto' => true,
                    ]);
                    
                    $auction->current_price = $autoBidAmount;

                    // Anti-Sniping: Si quedan 60 segundos o menos, extender 3 minutos
                    if (now()->diffInSeconds($auction->end_time) <= 60) {
                        $auction->end_time = $auction->end_time->addMinutes(3);
                    }
                    $auction->save();

                    broadcast(new \App\Events\BidPlaced($autoBid, $auction))->toOthers();

                    // Si todavía hay posibilidad de OTRA guerra de proxies, despachar el job de nuevo
                    $delay = rand(3, 6);
                    $secondsLeft = now()->diffInSeconds($auction->end_time);
                    if ($secondsLeft < $delay) {
                        $delay = max(0, $secondsLeft - 1);
                    }
                    if ($delay > 0) {
                        dispatch(new \App\Jobs\ProcessProxyBids($auction->id))->delay(now()->addSeconds($delay));
                    } else {
                        dispatch(new \App\Jobs\ProcessProxyBids($auction->id));
                    }
                }
            }
        });
    }
}
