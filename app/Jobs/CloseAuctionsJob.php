<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Auction;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class CloseAuctionsJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Iniciar subastas pendientes
        $pendingAuctions = Auction::where('status', 'pending')
            ->where('start_time', '<=', now())
            ->get();

        foreach ($pendingAuctions as $auction) {
            DB::transaction(function () use ($auction) {
                $auction = Auction::where('id', $auction->id)->lockForUpdate()->first();
                if ($auction->status !== 'pending') return;

                $auction->status = 'active';
                $auction->save();
                
                event(new \App\Events\AuctionStatusChanged($auction));
            });
        }

        // 2. Cerrar subastas activas expiradas
        $expiredAuctions = Auction::where('status', 'active')
            ->where('end_time', '<=', now())
            ->get();

        foreach ($expiredAuctions as $auction) {
            DB::transaction(function () use ($auction) {
                // Bloquear para actualización
                $auction = Auction::where('id', $auction->id)->lockForUpdate()->first();
                if ($auction->status !== 'active') return;

                $highestBid = $auction->bids()->orderByDesc('amount')->first();
                
                if ($highestBid) {
                    $auction->winner_user_id = $highestBid->user_id;
                    $auction->status = 'finished';
                    $auction->save();

                    $winner = $highestBid->user;

                    // Crear Reserva (Reservation)
                    Reservation::create([
                        'generator_id' => $auction->generator_id,
                        'branch_id' => $auction->generator->current_branch_id ?? $auction->generator->assigned_branch_id ?? 1, // Default to a branch if null
                        'client_name' => $winner->name,
                        'client_phone' => $winner->phone ?? 'N/A', // O el campo que exista en User
                        'expires_at' => $auction->payment_deadline ?? now()->addHours(24),
                        'is_active' => true,
                    ]);

                    // Actualizar status del generador
                    $generator = $auction->generator;
                    $generator->status = 'Separado';
                    $generator->save();
                    
                    event(new \App\Events\AuctionStatusChanged($auction));
                } else {
                    // Nadie pujó
                    $auction->status = 'finished'; // O 'cancelled'
                    $auction->save();

                    // Regresar a Disponible
                    $generator = $auction->generator;
                    $generator->status = 'Disponible';
                    $generator->save();
                    
                    event(new \App\Events\AuctionStatusChanged($auction));
                }
            });
        }
    }
}
