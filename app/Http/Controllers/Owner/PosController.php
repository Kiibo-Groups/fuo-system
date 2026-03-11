<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Generator;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\GeneratorStatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Lista los productos disponibles y permite fijar su precio de venta.
     */
    public function products(Request $request)
    {
        $userBranchId = Auth::user()->branch_id;
        
        $query = Generator::where('current_branch_id', $userBranchId)
                          ->where('status', 'Disponible');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('internal_folio', 'like', "%{$search}%");
            });
        }

        $generators = $query->latest()->paginate(15);

        return view('owner.pos.products', compact('generators'));
    }

    /**
     * Actualiza el precio de venta de un generador.
     */
    public function updatePrice(Request $request, Generator $generator)
    {
        $request->validate([
            'sale_price' => 'required|numeric|min:0'
        ]);

        $generator->update([
            'sale_price' => $request->sale_price
        ]);

        return back()->with('success', 'Precio de venta actualizado exitosamente.');
    }

    /**
     * Vista del Punto de Venta (POS)
     */
    public function index()
    {
        $userBranchId = Auth::user()->branch_id;
        
        // Solo enviamos los que ya tienen un precio de venta mayor a 0 y están disponibles
        $generators = Generator::where('current_branch_id', $userBranchId)
            ->where('status', 'Disponible')
            ->whereNotNull('sale_price')
            ->where('sale_price', '>', 0)
            ->get();

        return view('owner.pos.index', compact('generators'));
    }

    /**
     * Procesa la venta de los equipos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.generator_id' => 'required|exists:generators,id',
            'client_name' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'total_amount' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::create([
                'branch_id' => Auth::user()->branch_id,
                'user_id' => Auth::id(),
                'client_name' => $request->client_name ?? 'Cliente Mostrador',
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method
            ]);

            foreach ($request->items as $item) {
                $generator = Generator::findOrFail($item['generator_id']);
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'sellable_id' => $generator->id,
                    'sellable_type' => Generator::class,
                    'unit_price' => $generator->sale_price,
                    'quantity' => 1,
                    'subtotal' => $generator->sale_price
                ]);

                // Actualizar estatus del generador
                $generator->update([
                    'status' => 'Vendido'
                ]);

                // Registrar histórico
                GeneratorStatusHistory::create([
                    'generator_id' => $generator->id,
                    'user_id' => Auth::id(),
                    'previous_status' => 'Disponible',
                    'new_status' => 'Vendido',
                    'comment' => 'Venta registrada en POS. Ticket #' . $sale->id
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta completada exitosamente.',
                'sale_id' => $sale->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista de ventas del POS para la sucursal del dueño actual.
     */
    public function sales()
    {
        $userBranchId = Auth::user()->branch_id;
        $sales = Sale::where('branch_id', $userBranchId)
                     ->with(['items.sellable', 'user'])
                     ->latest()
                     ->paginate(15);

        return view('owner.pos.sales', compact('sales'));
    }

    /**
     * Muestra el ticket en un formato amigable para impresora térmica u otra.
     */
    public function printTicket(Sale $sale)
    {
        if ($sale->branch_id !== Auth::user()->branch_id && Auth::user()->role !== 'admin') {
            abort(403, 'No autorizado.');
        }

        $sale->load(['items.sellable', 'user', 'branch']);

        return view('owner.pos.ticket', compact('sale'));
    }
}
