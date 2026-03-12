<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Generator;
use App\Models\Branch;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.role:admin']);
    }

    public function index(Request $request)
    {
        // ─── VENTAS ──────────────────────────────────────────────────────────
        $salesQuery = SaleItem::with(['sale.branch', 'sale.user', 'sellable'])
            ->where('sellable_type', Generator::class)
            ->whereHas('sale');

        if ($request->filled('branch_id')) {
            $salesQuery->whereHas('sale', fn($q) => $q->where('branch_id', $request->branch_id));
        }
        if ($request->filled('date_from')) {
            $salesQuery->whereHas('sale', fn($q) => $q->whereDate('created_at', '>=', $request->date_from));
        }
        if ($request->filled('date_to')) {
            $salesQuery->whereHas('sale', fn($q) => $q->whereDate('created_at', '<=', $request->date_to));
        }

        $saleItems = $salesQuery->orderByDesc(
            Sale::select('created_at')->whereColumn('sales.id', 'sale_items.sale_id')->limit(1)
        )->paginate(50)->withQueryString();

        // ─── KPIs DE VENTAS ──────────────────────────────────────────────────
        $kpiItems = SaleItem::with('sellable')
            ->where('sellable_type', Generator::class)
            ->whereHas('sale', function ($q) use ($request) {
                if ($request->filled('branch_id'))  $q->where('branch_id', $request->branch_id);
                if ($request->filled('date_from'))  $q->whereDate('created_at', '>=', $request->date_from);
                if ($request->filled('date_to'))    $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->get();

        $totalRevenue    = $kpiItems->sum('subtotal');
        $totalCost       = $kpiItems->sum(fn($i) => optional($i->sellable)->cost ?? 0);
        $totalCommission = $kpiItems->sum(fn($i) => optional($i->sellable)->commission_amount ?? 0);
        $grossProfit     = $totalRevenue - $totalCost - $totalCommission; // Ganancia bruta

        // ─── GASTOS ──────────────────────────────────────────────────────────
        $expensesQuery = Expense::with('branch')->orderByDesc('expense_date');

        if ($request->filled('branch_id')) {
            $expensesQuery->where(function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id)->orWhereNull('branch_id');
            });
        }
        if ($request->filled('date_from')) {
            $expensesQuery->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $expensesQuery->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses     = $expensesQuery->get();
        $totalExpenses = $expenses->sum('amount');

        // ─── GANANCIA REAL (después de gastos) ───────────────────────────────
        $netProfit = $grossProfit - $totalExpenses;

        // ─── GASTOS POR CATEGORÍA (para chart/resumen) ───────────────────────
        $expensesByCategory = $expenses->groupBy('category')->map(fn($g) => $g->sum('amount'))->sortDesc();

        $branches = Branch::orderBy('name')->get();

        $expenseCategories = ['General', 'Flete / Logística', 'Nómina', 'Almacén', 'Marketing', 'Mantenimiento', 'Administrativo', 'Otro'];

        return view('admin.accounting.index', compact(
            'saleItems', 'branches',
            'totalRevenue', 'totalCost', 'totalCommission', 'grossProfit',
            'expenses', 'totalExpenses', 'netProfit',
            'expensesByCategory', 'expenseCategories'
        ));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'branch_id'    => 'nullable|exists:branches,id',
            'category'     => 'required|string|max:100',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        Expense::create([
            'created_by'   => Auth::id(),
            'branch_id'    => $request->branch_id ?: null,
            'category'     => $request->category,
            'description'  => $request->description,
            'amount'       => $request->amount,
            'expense_date' => $request->expense_date,
            'notes'        => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Gasto registrado correctamente.');
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();
        return redirect()->back()->with('success', 'Gasto eliminado.');
    }
}
