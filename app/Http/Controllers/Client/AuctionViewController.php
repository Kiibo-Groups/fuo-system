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
            ->visibleForUser(auth()->user())
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
        $user = auth()->user();
        if ($auction->branch_id !== null && $user->branch_id !== null && $auction->branch_id !== $user->branch_id) {
            abort(403, 'No tienes permiso para ver esta subasta.');
        }

        $auction->load(['generator.assignedBranch', 'winner', 'bids.user']);
        
        $registration = \App\Models\AuctionRegistration::where('auction_id', $auction->id)
            ->where('user_id', $user->id)
            ->first();
        
        $isRegistered = $registration && $registration->status === 'approved';
        $myMaxBid = $registration ? $registration->max_bid : null;

        return view('client.auctions.show', compact('auction', 'isRegistered', 'myMaxBid'));
    }
}
