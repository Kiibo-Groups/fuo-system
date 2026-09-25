<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuctionRegistrationController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        // Verificar si ya está inscrito
        $registration = AuctionRegistration::firstOrCreate(
            ['auction_id' => $auction->id, 'user_id' => auth()->id()],
            ['status' => 'pending', 'hold_amount' => $auction->guarantee_amount] // Hold dinámico
        );

        if ($registration->status === 'approved') {
            return back()->with('info', 'Ya estás inscrito en esta subasta.');
        }

        // Aquí se crearía la Preferencia de MercadoPago para la garantía de seriedad
        // Se puede hacer vía HTTP API si no se usa SDK
        /*
        $response = Http::withToken(env('MERCADOPAGO_ACCESS_TOKEN'))->post('https://api.mercadopago.com/checkout/preferences', [
            'items' => [
                [
                    'title' => 'Garantía de Seriedad - Subasta ' . $auction->id,
                    'quantity' => 1,
                    'unit_price' => (float) $auction->guarantee_amount,
                    'currency_id' => 'MXN'
                ]
            ],
            'back_urls' => [
                'success' => route('store.auctions.register.success', $auction->id),
                'failure' => route('store.auctions.show', $auction->id),
                'pending' => route('store.auctions.show', $auction->id)
            ],
            'auto_return' => 'approved',
            'purpose' => 'wallet_purchase'
        ]);
        
        if ($response->successful()) {
            return redirect($response->json()['init_point']);
        }
        */

        // SIMULACIÓN POR AHORA HASTA TENER CREDENCIALES
        $registration->update(['status' => 'approved', 'payment_id' => 'sim_'.uniqid()]);
        
        return back()->with('success', '¡Inscripción exitosa! Retención de $'.number_format($auction->guarantee_amount, 2).' MXN aplicada. Ya puedes pujar.');
    }

    public function success(Request $request, Auction $auction)
    {
        $registration = AuctionRegistration::where('auction_id', $auction->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Verificar el payment_id devuelto por MP
        $registration->update([
            'status' => 'approved',
            'payment_id' => $request->get('payment_id')
        ]);

        return redirect()->route('store.auctions.show', $auction)->with('success', 'Pago aprobado. Ya puedes pujar en la subasta.');
    }
}
