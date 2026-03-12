@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-6 lg:p-8">

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3 font-bold text-sm">
        <i class="fas fa-check-circle text-emerald-500 text-lg"></i> {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-bold">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}
    </div>
    @endif

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Contabilidad y Ganancias</h1>
            <p class="text-slate-500 font-medium mt-1">Análisis financiero: ventas, comisiones y gastos operativos.</p>
        </div>
        <button onclick="openExpenseModal()"
            class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs uppercase tracking-widest px-5 py-2.5 rounded-xl shadow-lg shadow-rose-500/20 transition-all">
            <i class="fas fa-minus-circle"></i> Registrar Gasto
        </button>
    </div>

    <!-- ===== KPI CARDS ===== -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4 mb-8">
        {{-- Ingresos --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-1">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ingresos</p>
            <p class="text-xl font-black text-slate-900">${{ number_format($totalRevenue, 2) }}</p>
            <p class="text-[10px] text-slate-400">Lo que pagaron los clientes</p>
        </div>
        {{-- Costo Real --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-1">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Costo Real</p>
            <p class="text-xl font-black text-rose-600">${{ number_format($totalCost, 2) }}</p>
            <p class="text-[10px] text-slate-400">Adquisición de unidades</p>
        </div>
        {{-- Comisiones --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-1">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Comisiones</p>
            <p class="text-xl font-black text-orange-500">${{ number_format($totalCommission, 2) }}</p>
            <p class="text-[10px] text-slate-400">Pagado a sucursales</p>
        </div>
        {{-- Ganancia Bruta --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-1">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ganancia Bruta</p>
            <p class="text-xl font-black {{ $grossProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">${{ number_format($grossProfit, 2) }}</p>
            <p class="text-[10px] text-slate-400">Ingreso − Costo − Comisión</p>
        </div>
        {{-- Gastos Operativos --}}
        <div class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5 flex flex-col gap-1 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-1 h-full bg-rose-400 rounded-r-2xl"></div>
            <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Gastos Operativos</p>
            <p class="text-xl font-black text-rose-600">${{ number_format($totalExpenses, 2) }}</p>
            <p class="text-[10px] text-slate-400">{{ $expenses->count() }} registros</p>
        </div>
        {{-- Ganancia Real --}}
        <div class="rounded-2xl shadow-lg p-5 flex flex-col gap-1 relative overflow-hidden
            {{ $netProfit >= 0 ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-emerald-500/20' : 'bg-gradient-to-br from-red-500 to-red-600 shadow-red-500/20' }}">
            <p class="text-[10px] font-black text-white/70 uppercase tracking-widest">Ganancia Real</p>
            <p class="text-xl font-black text-white">${{ number_format($netProfit, 2) }}</p>
            <p class="text-[10px] text-white/60">Bruta − Gastos operativos</p>
        </div>
    </div>

    <!-- ===== FILTROS ===== -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('admin.accounting.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Sucursal</label>
                <select name="branch_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-emerald-500 outline-none appearance-none">
                    <option value="">Todas las sucursales</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all shadow-sm">
                    <i class="fas fa-filter mr-1.5"></i> Filtrar
                </button>
                <a href="{{ route('admin.accounting.index') }}" class="px-4 py-2 bg-slate-100 text-slate-500 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-200 transition-all">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

        <!-- ===== TABLA DE VENTAS (2/3) ===== -->
        <div class="xl:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-50 flex items-center justify-between">
                <h3 class="font-black text-slate-800 uppercase tracking-tight text-sm">
                    <i class="fas fa-receipt mr-2 text-emerald-500"></i> Ventas de Generadores
                </h3>
                <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $saleItems->total() }} registros</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-slate-50/80 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                        <tr>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Sucursal</th>
                            <th class="px-4 py-3">Generador</th>
                            <th class="px-4 py-3 text-right text-rose-600">Costo</th>
                            <th class="px-4 py-3 text-center text-orange-600">Com.%</th>
                            <th class="px-4 py-3 text-right">Vendido</th>
                            <th class="px-4 py-3 text-right text-emerald-700">Ganancia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($saleItems as $item)
                        @php
                            $generator  = $item->sellable;
                            $cost       = $generator?->cost ?? 0;
                            $commAmt    = $generator?->commission_amount ?? 0;
                            $salePrice  = $item->unit_price;
                            $profit     = $salePrice - $cost - $commAmt;
                            $branch     = $item->sale?->branch;
                            $commRate   = $branch?->commission_rate ?? 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="text-xs font-bold text-slate-700">{{ $item->sale?->created_at?->format('d/m/Y') }}</div>
                                <div class="text-[9px] text-slate-400">{{ $item->sale?->created_at?->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-800 text-xs">{{ $branch?->name ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-black text-slate-900 tracking-tighter text-xs">{{ $generator?->internal_folio ?? '—' }}</div>
                                <div class="text-[9px] text-slate-400 uppercase">{{ $generator?->model }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs font-bold text-rose-600">${{ number_format($cost, 2) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block bg-orange-50 text-orange-600 border border-orange-100 text-[9px] font-black px-1.5 py-0.5 rounded-full">
                                    {{ number_format($commRate, 1) }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs font-black text-slate-900">${{ number_format($salePrice, 2) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-block px-2 py-0.5 rounded-lg text-xs font-black
                                    {{ $profit >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                                    {{ $profit >= 0 ? '+' : '' }}${{ number_format($profit, 2) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <i class="fas fa-chart-bar text-3xl text-slate-100 mb-3"></i>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Sin ventas registradas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($saleItems->total() > 0)
                    <tfoot class="bg-slate-900 text-white text-xs">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-[10px] font-black uppercase text-slate-400">Totales</td>
                            <td class="px-4 py-3 text-right text-rose-400 font-black">${{ number_format($totalCost, 2) }}</td>
                            <td></td>
                            <td class="px-4 py-3 text-right font-black">${{ number_format($totalRevenue, 2) }}</td>
                            <td class="px-4 py-3 text-right text-emerald-400 font-black">${{ number_format($grossProfit, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @if($saleItems->hasPages())
            <div class="p-4 border-t border-slate-50">{{ $saleItems->links() }}</div>
            @endif
        </div>

        <!-- ===== COLUMNA DERECHA: Gastos (1/3) ===== -->
        <div class="space-y-4">

            {{-- Resumen por categoría --}}
            @if($expensesByCategory->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h4 class="font-black text-slate-700 text-xs uppercase tracking-widest mb-4">
                    <i class="fas fa-tags mr-1.5 text-rose-500"></i> Gastos por Categoría
                </h4>
                @php $maxCat = $expensesByCategory->max(); @endphp
                @foreach($expensesByCategory as $cat => $amt)
                <div class="mb-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $cat }}</span>
                        <span class="text-[10px] font-black text-rose-600">${{ number_format($amt, 2) }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-rose-400 rounded-full transition-all"
                            style="width: {{ $maxCat > 0 ? round(($amt / $maxCat) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Lista de gastos --}}
            <div class="bg-white rounded-2xl border border-rose-100 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-50 flex items-center justify-between">
                    <h4 class="font-black text-slate-700 text-xs uppercase tracking-widest">
                        <i class="fas fa-minus-circle mr-1.5 text-rose-500"></i> Gastos Registrados
                    </h4>
                    <span class="bg-rose-100 text-rose-600 text-[9px] font-black px-2.5 py-1 rounded-full">
                        {{ $expenses->count() }}
                    </span>
                </div>
                <div class="divide-y divide-slate-50 max-h-[500px] overflow-y-auto">
                    @forelse($expenses as $expense)
                    <div class="p-4 hover:bg-slate-50/50 transition-colors group flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-block bg-rose-50 text-rose-600 border border-rose-100 text-[9px] font-black px-2 py-0.5 rounded-full uppercase">
                                    {{ $expense->category }}
                                </span>
                                <span class="text-[9px] text-slate-400">{{ $expense->expense_date->format('d/m/Y') }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $expense->description }}</p>
                            @if($expense->branch)
                            <p class="text-[9px] text-slate-400 mt-0.5 flex items-center gap-1">
                                <i class="fas fa-building text-[7px]"></i> {{ $expense->branch->name }}
                            </p>
                            @else
                            <p class="text-[9px] text-slate-300 mt-0.5">General / Todas las sucursales</p>
                            @endif
                            @if($expense->notes)
                            <p class="text-[9px] text-slate-400 italic mt-0.5">{{ $expense->notes }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-sm font-black text-rose-600">-${{ number_format($expense->amount, 2) }}</span>
                            <form action="{{ route('admin.accounting.expenses.destroy', $expense->id) }}" method="POST"
                                onsubmit="return confirm('¿Eliminar este gasto?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="opacity-0 group-hover:opacity-100 text-slate-300 hover:text-red-500 transition-all p-1">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center">
                        <i class="fas fa-check-circle text-2xl text-emerald-200 mb-2"></i>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Sin gastos registrados</p>
                    </div>
                    @endforelse
                </div>

                {{-- Total gastos --}}
                @if($expenses->count() > 0)
                <div class="p-4 border-t border-rose-100 bg-rose-50 flex items-center justify-between">
                    <span class="text-[10px] font-black text-rose-700 uppercase tracking-widest">Total Gastos</span>
                    <span class="font-black text-rose-700 text-sm">-${{ number_format($totalExpenses, 2) }}</span>
                </div>
                @endif
            </div>

            {{-- Resumen final --}}
            <div class="bg-slate-900 rounded-2xl p-5 space-y-3">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Resumen del Período</h4>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Ganancia Bruta</span>
                    <span class="font-black {{ $grossProfit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">${{ number_format($grossProfit, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">− Gastos Operativos</span>
                    <span class="font-black text-rose-400">-${{ number_format($totalExpenses, 2) }}</span>
                </div>
                <div class="border-t border-slate-700 pt-3 flex justify-between">
                    <span class="text-white font-black text-sm">Ganancia Real</span>
                    <span class="font-black text-lg {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">${{ number_format($netProfit, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Registrar Gasto ===== -->
<div id="modal-expense" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-rose-100">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-rose-600 to-rose-500">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-black text-white uppercase tracking-tight">Registrar Gasto</h3>
                    <p class="text-[10px] text-rose-200 mt-1">Se descontará de la ganancia bruta del período.</p>
                </div>
                <button onclick="closeExpenseModal()" class="text-rose-200 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('admin.accounting.expenses.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Categoría *</label>
                    <select name="category" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-rose-500 outline-none appearance-none">
                        @foreach($expenseCategories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Fecha *</label>
                    <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-rose-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Descripción *</label>
                <input type="text" name="description" required placeholder="Ej. Pago de flete contenedor Marzo..."
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-rose-500 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Monto (MXN) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">$</span>
                        <input type="number" name="amount" required min="0.01" step="0.01" placeholder="0.00"
                            class="w-full pl-7 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-rose-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Sucursal (Opcional)</label>
                    <select name="branch_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-rose-500 outline-none appearance-none">
                        <option value="">General / Todas</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Notas (Opcional)</label>
                <input type="text" name="notes" placeholder="Información adicional..."
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-rose-500 outline-none text-slate-700">
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeExpenseModal()"
                    class="flex-1 py-3 text-xs font-black text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase border border-slate-200">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 py-3 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-lg shadow-rose-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Registrar Gasto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openExpenseModal() {
        document.getElementById('modal-expense').classList.remove('hidden');
    }
    function closeExpenseModal() {
        document.getElementById('modal-expense').classList.add('hidden');
    }
    document.getElementById('modal-expense').addEventListener('click', function(e) {
        if (e.target === this) closeExpenseModal();
    });
</script>
@endsection
