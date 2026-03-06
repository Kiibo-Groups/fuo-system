<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Generator;
use App\Models\SparePart;
use App\Models\WorkshopLog;
use App\Models\GeneratorRevision;
use App\Models\GeneratorStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WorkshopController extends Controller
{
    public function index()
    {
        // Mostrar generadores que están "En taller" y/o historial de taller
        $generators = Generator::where('status', 'En taller')->get();
        return view('operations.workshop.index', compact('generators'));
    }

    public function create(Request $request)
    {
        $generator_id = $request->query('generator_id');
        $generator = Generator::findOrFail($generator_id);
        
        // Si no está en taller, redirigir
        if ($generator->status !== 'En taller') {
            return redirect()->route('operations.workshop.index')
                             ->with('error', 'El generador seleccionado no se encuentra en el taller.');
        }

        $spareParts = SparePart::where('stock', '>', 0)->get();

        return view('operations.workshop.create', compact('generator', 'spareParts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'generator_id' => 'required|exists:generators,id',
            'diagnosis' => 'required|string',
            'repair_result' => 'required|in:fixed,failed',
            'parts' => 'nullable|array',
            'parts.*.id' => 'required_with:parts|exists:spare_parts,id',
            'parts.*.quantity' => 'required_with:parts|integer|min:1'
        ]);

        $generator = Generator::findOrFail($request->generator_id);
        
        if ($generator->status !== 'En taller') {
            return back()->with('error', 'Este generador no está actualmente en el taller.');
        }

        DB::beginTransaction();
        try {
            $totalRepairCost = 0;
            
            // Crear el log de taller
            $workshopLog = WorkshopLog::create([
                'generator_id' => $generator->id,
                'technician_id' => Auth::id(),
                'diagnosis' => $request->diagnosis,
                'total_repair_cost' => 0, // Se actualizará más adelante
                'completed_at' => now(),
            ]);

            // Procesar las refacciones utilizadas
            if (!empty($request->parts)) {
                foreach ($request->parts as $partData) {
                    $part = SparePart::lockForUpdate()->find($partData['id']);
                    
                    if ($part->stock < $partData['quantity']) {
                        throw new \Exception("Stock insuficiente para la refacción: {$part->name}");
                    }

                    // Descontar del stock
                    $part->stock -= $partData['quantity'];
                    $part->save();

                    // Costo en el momento de la reparación
                    $costAtMoment = $part->unit_cost;
                    $lineTotal = $costAtMoment * $partData['quantity'];
                    $totalRepairCost += $lineTotal;

                    // Adjuntar a la tabla pivote workshop_spare_part
                    $workshopLog->sparePartsLog()->create([
                        'spare_part_id' => $part->id,
                        'quantity' => $partData['quantity'],
                        'cost_at_moment' => $costAtMoment
                    ]);
                }
            }

            // Actualizar costo de reparación
            $workshopLog->update(['total_repair_cost' => $totalRepairCost]);

            // Actualizar estado del generador para enviarlo o marcarlo como fallido
            $previousStatus = $generator->status;
            $newStatus = $request->repair_result === 'fixed' ? 'Lista para envío' : 'No procesado';
            $generator->update(['status' => $newStatus]);

            // Trazabilidad
            GeneratorStatusHistory::create([
                'generator_id' => $generator->id,
                'user_id' => Auth::id(),
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'comment' => 'Reparación completada en taller. Diagnóstico: ' . $request->diagnosis . '. Costo reparación: $' . number_format($totalRepairCost, 2)
            ]);

            DB::commit();

            $msg = $request->repair_result === 'fixed' 
                   ? 'Diagnóstico guardado y generador reparado exitosamente. Ahora está listo para envío.' 
                   : 'Diagnóstico guardado. El generador se marcó como sin arreglo (No procesado).';

                   
            // Guardar el resultado de la revisión
            GeneratorRevision::create([
                'generator_id' => $generator->id,
                'technician_id' => Auth::id(),
                'checklist_results' => [], // se casteará a JSON automáticamente en el modelo
                'observations' => $request->diagnosis,
                'result' => $request->repair_result === 'fixed' ? 'Aprobada' : 'No Aprobada'
            ]);

            return redirect()->route('operations.workshop.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ]);
            // return back()->with('error', 'Error al procesar el taller: ' . $e->getMessage());
        }
    }

    public function togglePayment(WorkshopLog $workshop)
    {
        // Solo administradores u owners pueden marcar como pagado/no pagado
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $workshop->update([
            'is_paid' => !$workshop->is_paid
        ]);

        return redirect()->back()->with('success', 'Estado de pago actualizado correctamente.');
    }
}
