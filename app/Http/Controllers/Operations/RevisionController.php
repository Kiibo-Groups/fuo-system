<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Generator;
use App\Models\ChecklistTemplate;
use App\Models\GeneratorRevision;
use App\Models\GeneratorStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RevisionController extends Controller
{
    public function scan(Request $request)
    {
        $folio = $request->query('folio');
        $generator = null;
        $checklist = null;

        if ($folio) {
            $generator = Generator::where('internal_folio', $folio)
                ->orWhere('serial_number', $folio)
                ->orWhere('id', $folio)
                ->first();

            if ($generator) {
                // Obtenemos el checklist activo más reciente
                $checklist = ChecklistTemplate::where('is_active', true)->latest()->first();
                
                // Opcional: Validar que el equipo pueda ser revisado, por ejemplo, los que están recién llegados
                if ($generator->status !== 'En revisión' && $generator->status !== 'Recibido en almacén') {
                    // Si se desea, se le puede permitir, pero por regla de negocio usualmente pasan a "En revisión" 
                    // cuando se reciben.
                    
                    // Si el estado aun no es "En revisión" (ej. Recibido en almacén), lo pasamos al escanear? 
                    // O lo hacemos explícito. Por ahora solo dejamos un mensaje si se desea, o lo actualizamos en store.
                }
            }
        }

        return view('operations.revisions.scan', compact('generator', 'checklist', 'folio'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'generator_id' => 'required|exists:generators,id',
            'checklist_results' => 'required|array',
            'observations' => 'nullable|string',
            'result' => 'required|in:Aprobada,No Aprobada'
        ]);

        $generator = Generator::findOrFail($request->generator_id);
        
        DB::beginTransaction();
        try {
            // Guardar el resultado de la revisión
            GeneratorRevision::create([
                'generator_id' => $generator->id,
                'technician_id' => Auth::id(),
                'checklist_results' => $request->checklist_results, // se casteará a JSON automáticamente en el modelo
                'observations' => $request->observations,
                'result' => $request->result
            ]);

            // Cambiar de estado dependiendo del resultado ("Lista para envío" o "En taller")
            $previousStatus = $generator->status;
            $newStatus = $request->result === 'Aprobada' ? 'Lista para envío' : 'En taller';
            
            $generator->update(['status' => $newStatus]);

            // Registrar Trazabilidad (como exige la regla 3.3)
            GeneratorStatusHistory::create([
                'generator_id' => $generator->id,
                'user_id' => Auth::id(),
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'comment' => $request->result === 'Aprobada' 
                    ? 'Revisión técnica aprobada. ' . $request->observations
                    : 'Revisión técnica fallida, requiere entrada a taller. ' . $request->observations
            ]);

            DB::commit();

            // Si falló, redirigir al taller para hacer el diagnóstico
            if ($request->result === 'No Aprobada') {
                return redirect()->route('operations.workshop.create', ['generator_id' => $generator->id])
                ->with('error', 'Revisión fallida. El generador requiere reparación. Ingrese el diagnóstico en el taller.');
            }

            return redirect()->route('operations.revisions.scan')->with('success', 'Revisión completada exitosamente. Generador listo para envío.');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'data' => $request->all(),
                'message' => 'Error al guardar la revisión: ' . $e->getMessage()
            ]);
            // return back()->with('error', 'Error al guardar la revisión: ' . $e->getMessage());
        }
    }
}
