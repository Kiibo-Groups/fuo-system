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

            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
            ]);

            $auction->current_price = $request->amount;
            $auction->save();

            DB::commit();

            // Emitir evento
            broadcast(new BidPlaced($bid, $auction))->toOthers();

            return response()->json([
                'message' => 'Puja realizada con éxito.',
                'bid' => $bid,
                'current_price' => $auction->current_price
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ocurrió un error al procesar la puja.'], 500);
        }
    }
}
