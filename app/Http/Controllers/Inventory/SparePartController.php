<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SparePart;

class SparePartController extends Controller
{
    public function index()
    {
        $spareParts = SparePart::orderBy('id', 'desc')->get();
        return view('admin.inventory.spare_parts.index', compact('spareParts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        SparePart::create($validated);

        return redirect()->route('inventory.spare-parts.index')->with('success', 'Refacción creada correctamente.');
    }

    public function update(Request $request, SparePart $sparePart)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $sparePart->update($validated);

        return redirect()->route('inventory.spare-parts.index')->with('success', 'Refacción actualizada correctamente.');
    }

    public function destroy(SparePart $sparePart)
    {
        $sparePart->delete();
        return redirect()->route('inventory.spare-parts.index')->with('success', 'Refacción eliminada correctamente.');
    }

    public function exportExcel()
    {
        $parts = SparePart::all();
        $fileName = 'inventario_refacciones_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Nombre/Descripción', 'Stock Actual', 'Costo Unitario (MXN)', 'Umbral Bajo'];

        $callback = function() use($parts, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($parts as $part) {
                fputcsv($file, [
                    $part->id,
                    $part->name,
                    $part->stock,
                    $part->unit_cost,
                    $part->low_stock_threshold
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
