<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Auction;

class AuctionViewController extends Controller
{
    /**
     * Listado de subastas para clientes.
     */
    public function index()
    {
        $auctions = Auction::with(['generator', 'bids'])
            ->whereIn('status', ['active', 'pending', 'finished'])
            ->orderByRaw("FIELD(status, 'active', 'pending', 'finished')")
            ->orderBy('end_time', 'asc')
            ->get();

        return view('client.auctions.index', compact('auctions'));
    }

    /**
     * Vista de sala de subasta en vivo.
     */
    public function show(Auction $auction)
    {
        $auction->load(['generator.assignedBranch', 'winner', 'bids.user']);
        return view('client.auctions.show', compact('auction'));
    }
}
