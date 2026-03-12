@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-4 lg:p-8">
    <!-- Encabezado y Resumen -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Listado de Ventas</h1>
            <p class="text-slate-500 font-medium text-sm mt-1">Historial de las ventas realizadas en tu sucursal.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('owner.pos.index') }}" class="bg-orange-600 border border-orange-600 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-sm hover:bg-orange-700 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Nueva Venta
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Tabla de Ventas -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Ticket / Fecha</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4">Método de Pago</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-900 tracking-tighter">#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $sale->created_at->format('d/m/Y h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-700 uppercase text-xs">{{ $sale->client_name ?: 'Cliente Mostrador' }}</div>
                            <div class="text-[10px] text-slate-400 font-medium">Vendedor: {{ $sale->user->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase border border-slate-200 bg-slate-50 tracking-tighter shadow-sm text-slate-600">
                                <i class="fas @if($sale->payment_method=='Efectivo') fa-money-bill-wave @elseif($sale->payment_method=='Transferencia') fa-university @else fa-credit-card @endif mr-1"></i> {{ $sale->payment_method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-black text-slate-900 tracking-tight">
                            ${{ number_format($sale->total_amount, 2) }} <span class="text-[10px] text-slate-400 font-normal">MXN</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="printTicket({{ $sale->id }})" class="p-2 text-slate-400 hover:text-orange-600 bg-white border border-slate-100 rounded-lg shadow-sm" title="Imprimir Ticket">
                                <i class="fas fa-print text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-receipt text-4xl text-slate-200 mb-4"></i>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No hay ventas registradas</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        @if($sales->hasPages())
        <div class="p-6 bg-slate-50/50 border-t border-slate-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    function printTicket(id) {
        const printWindow = window.open(`/pos/sales/${id}/ticket`, '_blank', 'width=400,height=600');
        if (printWindow) {
            printWindow.focus();
        } else {
            alert('Por favor permite las ventanas emergentes (pop-ups) para imprimir el ticket.');
        }
    }
</script>
@endsection
