<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Generator;
use App\Models\Shipment;
use App\Models\ShipmentBatch;
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
            // El dueño ve los LOTES dirigidos a su sucursal (con generadores en status 'Enviado')
            $userBranchId = Auth::user()->branch_id;

            $incomingBatches = ShipmentBatch::with(['shipments.generator'])
                ->whereHas('shipments.generator', function ($query) use ($userBranchId) {
                    $query->where('status', 'Enviado')
                          ->where('assigned_branch_id', $userBranchId);
                })
                ->latest()
                ->get();

            return view('owner.shipments.index', compact('incomingBatches'));
        }

        // Admin: unidades listas para envío
        $readyToShip = Generator::where('status', 'Lista para envío')
            ->with(['branch', 'assignedBranch'])
            ->orderBy('assigned_branch_id')
            ->get();

        // Admin: lotes de envío activos (en tránsito)
        $activeBatches = ShipmentBatch::with(['shipments.generator.assignedBranch', 'creator'])
            ->whereHas('shipments.generator', function ($query) {
                $query->where('status', 'Enviado');
            })
            ->latest()
            ->get();

        return view('admin.logistics.shipments.index', compact('readyToShip', 'activeBatches'));
    }

    /**
     * Despacha un LOTE de generadores con una sola guía y paquetería.
     */
    public function sendBatch(Request $request)
    {
        $request->validate([
            'generator_ids'   => 'required|string',
            'shipping_company' => 'required|string',
            'tracking_number' => 'required|string',
            'evidences'       => 'nullable|array',
            'evidences.*'     => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'notes'           => 'nullable|string|max:500',
        ]);

        $ids = array_filter(explode(',', $request->generator_ids));

        if (empty($ids)) {
            return redirect()->back()->withErrors(['Debes seleccionar al menos un generador.']);
        }

        DB::transaction(function () use ($request, $ids) {
            // 1. Guardar evidencias
            $paths = [];
            if ($request->hasFile('evidences')) {
                foreach ($request->file('evidences') as $file) {
                    $paths[] = $file->store('shipments/evidence', 'public');
                }
            }

            // 2. Crear el lote de envío
            $batch = ShipmentBatch::create([
                'created_by'         => Auth::id(),
                'shipping_company'   => $request->shipping_company,
                'tracking_number'    => $request->tracking_number,
                'photo_evidence_path' => count($paths) > 0 ? $paths[0] : null,
                'evidences'          => $paths,
                'notes'              => $request->notes,
            ]);

            // 3. Procesar cada generador
            $generators = Generator::whereIn('id', $ids)->get();

            foreach ($generators as $generator) {
                $oldStatus = $generator->status;

                // Crear el registro individual de shipment, ligado al lote
                Shipment::create([
                    'generator_id'       => $generator->id,
                    'shipment_batch_id'  => $batch->id,
                    'shipping_company'   => $request->shipping_company,
                    'tracking_number'    => $request->tracking_number,
                    'photo_evidence_path' => count($paths) > 0 ? $paths[0] : null,
                    'evidences'          => $paths,
                ]);

                // Actualizar generador: status Enviado + current_branch = sucursal destino
                $generator->update([
                    'status'            => 'Enviado',
                    'current_branch_id' => $generator->assigned_branch_id,
                ]);

                // Historial
                GeneratorStatusHistory::create([
                    'generator_id'    => $generator->id,
                    'user_id'         => Auth::id(),
                    'previous_status' => $oldStatus,
                    'new_status'      => 'Enviado',
                    'comment'         => "Lote #{$batch->id} despachado vía {$request->shipping_company} | Guía: {$request->tracking_number}.",
                ]);
            }
        });

        return redirect()->back()->with('success', 'Lote despachado correctamente con ' . count($ids) . ' generadores.');
    }

    /**
     * Confirma la recepción de un LOTE COMPLETO en la sucursal.
     */
    public function receiveBatch(Request $request, ShipmentBatch $batch)
    {
        DB::transaction(function () use ($batch) {
            // Obtener todos los generadores de los shipments de este lote
            $generatorIds = $batch->shipments()->pluck('generator_id')->toArray();
            $generators = Generator::whereIn('id', $generatorIds)->get();

            foreach ($generators as $generator) {
                $oldStatus = $generator->status;

                $generator->update([
                    'status'            => 'Disponible',
                    'current_branch_id' => $generator->assigned_branch_id,
                ]);

                GeneratorStatusHistory::create([
                    'generator_id'    => $generator->id,
                    'user_id'         => Auth::id(),
                    'previous_status' => $oldStatus,
                    'new_status'      => 'Disponible',
                    'comment'         => "Recepción confirmada del Lote #{$batch->id} | Guía: {$batch->tracking_number}.",
                ]);
            }
        });

        $count = $batch->shipments()->count();
        return redirect()->back()->with('success', "Lote #{$batch->id} recibido correctamente. {$count} unidades disponibles en inventario.");
    }

    /**
     * @deprecated - Mantenido por compatibilidad. Usar sendBatch.
     */
    public function sendToBranch(Request $request)
    {
        return $this->sendBatch($request);
    }

    /**
     * @deprecated - Mantenido por compatibilidad. Usar receiveBatch.
     */
    public function receiveAtBranch(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        if ($shipment->shipment_batch_id) {
            $batch = ShipmentBatch::findOrFail($shipment->shipment_batch_id);
            return $this->receiveBatch($request, $batch);
        }
        // Fallback individual
        $generator = Generator::findOrFail($shipment->generator_id);
        $oldStatus = $generator->status;
        $generator->update(['status' => 'Disponible', 'current_branch_id' => $generator->assigned_branch_id]);
        GeneratorStatusHistory::create([
            'generator_id' => $generator->id, 'user_id' => Auth::id(),
            'previous_status' => $oldStatus, 'new_status' => 'Disponible',
            'comment' => 'Recepción individual en sucursal.',
        ]);
        return redirect()->back()->with('success', 'Equipo recibido correctamente.');
    }
}