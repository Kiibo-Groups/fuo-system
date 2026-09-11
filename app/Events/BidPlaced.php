<?php

namespace App\Events;

use App\Models\Bid;
use App\Models\Auction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bid;
    public $auction;

    /**
     * Create a new event instance.
     */
    public function __construct(Bid $bid, Auction $auction)
    {
        $this->bid = $bid;
        $this->auction = $auction;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction.' . $this->auction->id),
        ];
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'bid' => [
                'id' => $this->bid->id,
                'amount' => $this->bid->amount,
                'user_name' => $this->bid->user->name,
                'created_at' => $this->bid->created_at->toDateTimeString(),
            ],
            'current_price' => $this->auction->current_price,
            'bids_count' => $this->auction->bids()->count(),
        ];
    }
}
