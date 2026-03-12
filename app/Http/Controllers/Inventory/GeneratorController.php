<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Generator;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\GeneratorStatusHistory;
use Illuminate\Support\Facades\Auth;

class GeneratorController extends Controller
{
    public function updateStatus(Request $request, Generator $generator)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'nullable|string|max:500',
            'current_branch_id' => 'nullable|string'
        ]);

        $oldStatus = $generator->status;

        $newBranchId = $request->current_branch_id;
        if ($newBranchId === 'none') {
            $newBranchId = null;
        } elseif (empty($newBranchId)) {
            $newBranchId = $generator->current_branch_id;
        }

        $generator->update([
            'status' => $request->status,
            'current_branch_id' => $newBranchId
        ]);

        GeneratorStatusHistory::create([
            'generator_id' => $generator->id,
            'user_id' => Auth::id(),
            'previous_status' => $oldStatus,
            'new_status' => $request->status,
            'comment' => $request->comment
        ]);

        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }

    public function batchUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'generator_ids' => 'required|string',
            'status' => 'nullable|string',
            'current_branch_id' => 'nullable|string',
            'comment' => 'nullable|string|max:500'
        ]);

        if (empty($validated['status']) && empty($validated['current_branch_id'])) {
            return redirect()->back()->withErrors(['Debe seleccionar un estado o una sucursal para actualizar.']);
        }

        $ids = explode(',', $validated['generator_ids']);
        $generators = Generator::whereIn('id', $ids)->get();

        foreach ($generators as $generator) {
            $oldStatus = $generator->status;

            $updateData = [];
            if (!empty($validated['status'])) {
                $updateData['status'] = $validated['status'];
            }
            if (!empty($validated['current_branch_id'])) {
                $updateData['current_branch_id'] = $validated['current_branch_id'] === 'none' ? null : $validated['current_branch_id'];
            }

            if (!empty($updateData)) {
                $generator->update($updateData);
            }

            if (!empty($validated['status']) && $oldStatus !== $validated['status']) {
                GeneratorStatusHistory::create([
                    'generator_id' => $generator->id,
                    'user_id' => Auth::id(),
                    'previous_status' => $oldStatus,
                    'new_status' => $validated['status'],
                    'comment' => $validated['comment'] ?? 'Actualización por lote'
                ]);
            }
        }

        return redirect()->back()->with('success', count($generators) . ' generadores actualizados correctamente.');
    }
    public function index(Request $request)
    {
        $query = Generator::with('branch');

        if (Auth::user()->role === 'owner') {
            $query->where('current_branch_id', Auth::user()->branch_id);
        }

        // Filtro por búsqueda rápida (Modelo, Serie o Folio)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('internal_folio', 'like', "%{$search}%");
            });
        }

        // Filtro por Sucursal
        if ($request->filled('branch_id')) {
            $query->where('current_branch_id', $request->branch_id);
        }

        // Filtro por Estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $generators = $query->orderBy('id', 'desc')->paginate(100);
        $branches = Branch::all();

        return view('admin.inventory.generators.index', compact('generators', 'branches'));
    }

    public function show(Generator $generator)
    {
        $generator->load(['branch', 'revisions.technician', 'workshopLogs.sparePartsLog.sparePart', 'statusHistory.user', 'currentReservation']);
        $branches = Branch::all();
        return view('admin.inventory.generators.show', compact('generator', 'branches'));
    }

    public function releaseReservation(Generator $generator)
    {
        if ($generator->status !== 'Separado') {
            return back()->withErrors(['El equipo no está separado.']);
        }

        $reservation = $generator->currentReservation;
        
        if ($reservation) {
            $reservation->update(['is_active' => false]);
        }

        $generator->update(['status' => 'Disponible']);

        // Registrar Historial
        \App\Models\GeneratorStatusHistory::create([
            'generator_id' => $generator->id,
            'user_id' => Auth::id(),
            'previous_status' => 'Separado',
            'new_status' => 'Disponible',
            'comment' => 'Separación liberada por ' . Auth::user()->name . ' (SuperAdmin).'
        ]);

        return back()->with('success', 'El equipo ha sido liberado exitosamente.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:generators,serial_number',
            'internal_folio' => 'required|string|unique:generators,internal_folio',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|string',
            'current_branch_id' => 'nullable|exists:branches,id',
        ]);

        Generator::create($validated);

        return redirect()->route('inventory.generators.index')->with('success', 'Generador creado correctamente.');
    }

    public function update(Request $request, Generator $generator)
    {
        $validated = $request->validate([
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:generators,serial_number,' . $generator->id,
            'internal_folio' => 'required|string|unique:generators,internal_folio,' . $generator->id,
            'cost' => 'required|numeric|min:0',
            'status' => 'required|string',
            'current_branch_id' => 'nullable|exists:branches,id',
        ]);

        $generator->update($validated);

        return redirect()->route('inventory.generators.index')->with('success', 'Generador actualizado correctamente.');
    }

    public function destroy(Generator $generator)
    {
        $generator->delete();
        return redirect()->route('inventory.generators.index')->with('success', 'Generador eliminado correctamente.');
    }

    public function batchDestroy(Request $request)
    {
        $validated = $request->validate([
            'generator_ids' => 'required|string',
        ]);

        $ids = explode(',', $validated['generator_ids']);
        $count = count($ids);

        $generators = Generator::whereIn('id', $ids)->get();
        foreach ($generators as $generator) {
            $generator->delete();
        }

        return redirect()->route('inventory.generators.index')->with('success', $count . ' generadores eliminados correctamente.');
    }

    public function createOrder()
    {
        $generators = Generator::where('status', 'Pedido en tránsito')->orderBy('id', 'desc')->get();
        return view('admin.inventory.generators.orders_usa', compact('generators'));
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:generators,serial_number',
            'internal_folio' => 'required|string|unique:generators,internal_folio',
            'cost' => 'required|numeric|min:0',
        ]);

        $validated['status'] = 'Pedido en tránsito';
        $validated['current_branch_id'] = null; // No sucursal yet

        Generator::create($validated);

        return redirect()->route('admin.orders.usa')->with('success', 'Pedido USA registrado correctamente.');
    }

    public function updateOrder(Request $request, Generator $generator)
    {
        $validated = $request->validate([
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:generators,serial_number,' . $generator->id,
            'internal_folio' => 'required|string|unique:generators,internal_folio,' . $generator->id,
            'cost' => 'required|numeric|min:0',
        ]);

        $generator->update($validated);

        return redirect()->route('admin.orders.usa')->with('success', 'Pedido USA actualizado correctamente.');
    }

    public function destroyOrder(Generator $generator)
    {
        $generator->delete();
        return redirect()->route('admin.orders.usa')->with('success', 'Pedido USA eliminado correctamente.');
    }

    public function exportExcel(Request $request)
    {
        $query = Generator::with('branch');

        if (Auth::user()->role === 'owner') {
            $query->where('current_branch_id', Auth::user()->branch_id);
        }

        // Aplicar los mismos filtros que en la tabla
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('internal_folio', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('current_branch_id', $request->branch_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $generators = $query->get();

        $fileName = 'inventario_global_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Folio Interno', 'No. Serie', 'Modelo', 'Costo (USD)', 'Sucursal', 'Estado', 'Fecha Registro'];

        $callback = function() use($generators, $columns) {
            $file = fopen('php://output', 'w');
            // Añadir el BOM para que Excel reconozca los acentos y UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);

            foreach ($generators as $gen) {
                fputcsv($file, [
                    $gen->id,
                    $gen->internal_folio,
                    $gen->serial_number,
                    $gen->model,
                    $gen->cost,
                    $gen->branch->name ?? 'En tránsito',
                    $gen->status,
                    $gen->created_at->format('Y-m-d H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $fileRealPath = $file->getRealPath();

        // Eliminar BOM de UTF-8 si existe
        $content = file_get_contents($fileRealPath);
        if (substr($content, 0, 3) == "\xEF\xBB\xBF") {
            file_put_contents($fileRealPath, substr($content, 3));
        }

        $imported = 0;
        $failed = 0;

        if (($handle = fopen($fileRealPath, "r")) !== FALSE) {
            $rowNum = 0;
            while (($rawLine = fgets($handle)) !== FALSE) {
                // Ensure the line has printable content and isn't just an empty newline
                $rawLine = trim($rawLine);
                if (empty($rawLine)) continue;

                $rowNum++;

                // Detect the separator (either semicolon or comma)
                $separator = (strpos($rawLine, ';') !== false) ? ';' : ',';
                
                // Parse the line manually to prevent an unescaped double quote from swallowing subsequent rows
                $data = str_getcsv($rawLine, $separator, '"', '\\');
                
                // Ignorar la cabecera
                if ($rowNum === 1 && (strtolower(trim($data[0] ?? '')) === 'folio-interno' || strtolower(trim($data[0] ?? '')) === 'folio' || strtolower(trim($data[2] ?? '')) === 'modelo')) {
                    continue;
                }

                $folio  = trim($data[0] ?? '');
                $serie  = trim($data[1] ?? '');
                $modelo = trim($data[2] ?? '');
                $costo  = trim($data[3] ?? '');

                // El modelo es el único campo estrictamente obligatorio
                if (empty($modelo) || strtoupper($modelo) === 'N/A' || strtoupper($modelo) === 'NA') {
                    $modelo = $modelo . '-' . $rowNum;
                }

                if (empty($folio) || strtoupper($folio) === 'N/A' || strtoupper($folio) === 'NA') {
                    $folio = 'FUO-IMP-' . strtoupper(substr(uniqid(), -6));
                }

                if (empty($serie) || strtoupper($serie) === 'N/A' || strtoupper($serie) === 'NA') {
                    $serie = 'FUO-SL-' . strtoupper(substr(uniqid(), -6));
                }

                // Limpiar caracteres del costo
                $costo = preg_replace('/[^0-9.]/', '', $costo);
                if (empty($costo)) {
                    $costo = 0;
                }

                try {
                    \App\Models\Generator::create([
                        'internal_folio' => $folio,
                        'serial_number'  => $serie,
                        'model'          => $modelo,
                        'cost'           => (float) $costo,
                        'status'         => 'Pedido en tránsito',
                        'current_branch_id' => null,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Import error on row {$rowNum}: " . $e->getMessage());
                    $failed++;
                }
            }
            fclose($handle);
        }

        return redirect()->route('inventory.generators.index')->with('success', "Importación finalizada. {$imported} procesados, {$failed} ignorados/duplicados.");
    }

    public function generateQRCode(Generator $generator)
    {
        return view('admin.inventory.generators.qr-print', compact('generator'));
    }

    public function availableInBranch(Request $request)
    {
        $userBranchId = Auth::user()->branch_id;
        
        $query = Generator::where('current_branch_id', $userBranchId)->where('status', 'Disponible');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $generators = $query->latest()->paginate(50);

        $activeBanners = \App\Models\Banner::where('is_active', true)
            ->whereIn('target_audience', ['client', 'both'])
            ->orderBy('order')
            ->get();

        return view('client.store.available', compact('generators', 'activeBanners'));
    }
}