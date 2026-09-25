<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\Bid;
use App\Events\BidPlaced;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0'
        ]);

        if ($auction->status !== 'active') {
            return response()->json(['message' => 'La subasta no está activa.'], 403);
        }

        if (now()->isAfter($auction->end_time)) {
            return response()->json(['message' => 'La subasta ha finalizado.'], 403);
        }

        $minAllowedBid = $auction->current_price > 0 ? $auction->current_price + $auction->min_increment : $auction->start_price;

        if ($request->amount < $minAllowedBid) {
            return response()->json([
                'message' => 'La puja debe ser de al menos $' . number_format($minAllowedBid, 2)
            ], 422);
        }

        // Usamos una transacción para asegurar la integridad de la puja
        DB::beginTransaction();
        try {
            // Refrescar y bloquear la fila para evitar concurrencia
            $auction = Auction::where('id', $auction->id)->lockForUpdate()->first();

            // Verificar de nuevo por si cambió
            $minAllowedBid = $auction->current_price > 0 ? $auction->current_price + $auction->min_increment : $auction->start_price;
            if ($request->amount < $minAllowedBid) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Alguien acaba de pujar. La puja debe ser de al menos $' . number_format($minAllowedBid, 2)
                ], 422);
            }

            $isProxy = $request->boolean('is_proxy');
            
            // Si es una puja automática, actualizamos la tabla registration
            if ($isProxy) {
                $registration = \App\Models\AuctionRegistration::where('auction_id', $auction->id)
                    ->where('user_id', auth()->id())
                    ->first();
                if ($registration) {
                    $registration->update(['max_bid' => $request->amount]);
                }
                
                // Si la cantidad ingresada como tope es mayor o igual a la puja mínima requerida, pujamos el mínimo
                $bidAmount = $minAllowedBid;
            } else {
                $bidAmount = $request->amount;
            }

            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => auth()->id(),
                'amount' => $bidAmount,
            ]);

            $auction->current_price = $bidAmount;
            
            // Anti-Sniping: Si quedan 60 segundos o menos, extender 3 minutos
            if (now()->diffInSeconds($auction->end_time) <= 60) {
                $auction->end_time = $auction->end_time->addMinutes(3);
            }
            
            $auction->save();

            DB::commit();

            // Emitir evento de la puja principal
            broadcast(new BidPlaced($bid, $auction))->toOthers();
            
            // Auto-bidding (Proxy Bidding) resolution - Despachar job con retraso
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

            return response()->json([
                'message' => $isProxy ? 'Auto-puja configurada con éxito.' : 'Puja realizada con éxito.',
                'bid' => $bid->load('user'),
                'current_price' => $auction->current_price
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ocurrió un error al procesar la puja.'], 500);
        }
    }
}
