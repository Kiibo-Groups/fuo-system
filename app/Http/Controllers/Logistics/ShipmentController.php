<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Generator;
use App\Models\Shipment;
use App\Models\GeneratorStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShipmentController extends Controller
{
    /**
     * Muestra la lista de envíos activos y equipos listos para enviar.
     */
    public function index()
    {
        if (Auth::user()->role === 'owner') {
            // El dueño solo ve los envíos dirigidos a su sucursal ("Enviado")
            $userBranchId = Auth::user()->branch_id; 
            
            $incomingShipments = Shipment::with(['generator'])
                ->whereHas('generator', function($query) use ($userBranchId) {
                    $query->where('status', 'Enviado')
                          ->where('current_branch_id', $userBranchId);
                })
                ->latest()
                ->get();

            return view('owner.shipments.index', compact('incomingShipments'));
        }

        // Unidades listas para envío (Admin las ve para despacharlas)
        $readyToShip = Generator::where('status', 'Lista para envío')->with('branch')->get();

        // Envíos que están actualmente en tránsito
        $activeShipments = Shipment::with(['generator.branch'])
            ->whereHas('generator', function($query) {
                $query->where('status', 'Enviado');
            })->latest()->get();

        return view('admin.logistics.shipments.index', compact('readyToShip', 'activeShipments'));
    }

    /**
     * Registra la salida de una unidad hacia una sucursal.
     */
    public function sendToBranch(Request $request)
    {
        $request->validate([
            'generator_id' => 'required|exists:generators,id',
            'shipping_company' => 'required|string',
            'tracking_number' => 'required|string',
            'evidences' => 'required|array|min:1',
            'evidences.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            $generator = Generator::findOrFail($request->generator_id);
            $oldStatus = $generator->status;

            // 1. Guardar las imagenes de evidencia
            $paths = [];
            if ($request->hasFile('evidences')) {
                foreach ($request->file('evidences') as $file) {
                    $paths[] = $file->store('shipments/evidence', 'public');
                }
            }

            // 2. Crear el registro del envío
            Shipment::create([
                'generator_id' => $generator->id,
                'shipping_company' => $request->shipping_company,
                'tracking_number' => $request->tracking_number,
                'photo_evidence_path' => count($paths) > 0 ? $paths[0] : null,
                'evidences' => $paths,
            ]);

            // 3. Actualizar el estado del Generador
            $generator->update([
                'status' => 'Enviado'
            ]);

            // 4. Registrar en el historial de trazabilidad
            GeneratorStatusHistory::create([
                'generator_id' => $generator->id,
                'user_id' => Auth::id(),
                'previous_status' => $oldStatus,
                'new_status' => 'Enviado',
                'comment' => "Envío despachado vía {$request->shipping_company} con guía {$request->tracking_number}."
            ]);
        });

        return redirect()->back()->with('success', 'El equipo ha sido despachado correctamente.');
    }

    /**
     * Confirma la recepción del equipo en la sucursal (Usuario 2).
     */
    public function receiveAtBranch(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $generator = Generator::findOrFail($id);
            $oldStatus = $generator->status;

            // 1. Actualizar estado a "Recibido en sucursal" o directamente a "Disponible"
            $generator->update([
                'status' => 'Disponible' // Al recibir, ya puede estar disponible para venta
            ]);

           
            // 2. Registrar en historial
            GeneratorStatusHistory::create([
                'generator_id' => $generator->id,
                'user_id' => Auth::id(),
                'previous_status' => $oldStatus,
                'new_status' => 'Disponible',
                'comment' => 'Equipo recibido físicamente en sucursal y puesto a la venta.'
            ]);
        });

        return redirect()->back()->with('success', 'Equipo recibido e ingresado al inventario disponible.');
    }
}