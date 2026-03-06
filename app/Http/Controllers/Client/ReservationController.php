<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Generator;
use App\Models\Reservation;
use App\Models\GeneratorStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function myReservations()
    {
        $reservations = Reservation::with('generator.branch')
            ->where('client_name', Auth::user()->name)
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('client.store.reservations', compact('reservations'));
    }

    public function reserve(Request $request, Generator $generator)
    {
        $request->validate([
            'client_phone' => 'required|string|max:20'
        ]);

        if ($generator->status !== 'Disponible') {
            return back()->with('error', 'El equipo seleccionado ya no está disponible.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $generator->status;

            // 1. Crear Separación
            Reservation::create([
                'generator_id' => $generator->id,
                'branch_id' => $generator->current_branch_id,
                'client_name' => Auth::user()->name,
                'client_phone' => $request->client_phone,
                'expires_at' => now()->addHours(4),
                'is_active' => true,
            ]);

            // 2. Cambiar a estado Separado
            $generator->update(['status' => 'Separado']);

            // 3. Registrar Trazabilidad
            GeneratorStatusHistory::create([
                'generator_id' => $generator->id,
                'user_id' => Auth::id(),
                'previous_status' => $oldStatus,
                'new_status' => 'Separado',
                'comment' => 'Separación autorizada por cliente ' . Auth::user()->name . '.'
            ]);

            DB::commit();

            return redirect()->route('store.reservations')->with('success', '¡Su equipo ha sido separado con éxito por las próximas 4 horas!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar la separación: ' . $e->getMessage());
        }
    }
}
