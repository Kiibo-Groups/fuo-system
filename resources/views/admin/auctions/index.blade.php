@extends('layouts.app')

@section('content')
<div class="p-6 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-200">
                    <i class="fas fa-gavel text-white text-sm"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Subastas</h1>
            </div>
            <p class="text-slate-500 text-sm ml-13">Gestiona las subastas de generadores en tiempo real.</p>
        </div>
        <a href="{{ route('admin.auctions.create') }}"
           class="flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-orange-200 hover:shadow-xl hover:scale-105 transition-all duration-200">
            <i class="fas fa-plus"></i> Nueva Subasta
        </a>
    </div>

    {{-- Status Filter Tabs --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
        @php
            $tabs = [
                'all'       => ['label' => 'Todas', 'icon' => 'fas fa-layer-group'],
                'active'    => ['label' => 'Activas', 'icon' => 'fas fa-bolt'],
                'pending'   => ['label' => 'Pendientes', 'icon' => 'fas fa-clock'],
                'finished'  => ['label' => 'Finalizadas', 'icon' => 'fas fa-check-circle'],
                'cancelled' => ['label' => 'Canceladas', 'icon' => 'fas fa-times-circle'],
            ];
            $currentFilter = request('status', 'all');
        @endphp
        @foreach($tabs as $key => $tab)
        <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-200
           {{ $currentFilter === $key
               ? 'bg-slate-900 text-white shadow-md'
               : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <i class="{{ $tab['icon'] }} text-xs
            {{ $currentFilter === $key ? '' : 'text-slate-400' }}"></i>
            {{ $tab['label'] }}
        </a>
        @endforeach
    </div>

    {{-- Auctions Table --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 overflow-x-auto">
            <table id="auctionsTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Generador</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Precio / Pujas</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Fechas</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($auctions as $auction)
                    @php
                        $statusConfig = [
                            'active'    => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'bgLight' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'label' => 'EN VIVO', 'pulse' => true],
                            'pending'   => ['bg' => 'bg-amber-500',   'text' => 'text-amber-600',   'bgLight' => 'bg-amber-50',   'border' => 'border-amber-200',   'label' => 'PRÓXIMA',  'pulse' => false],
                            'finished'  => ['bg' => 'bg-slate-400',   'text' => 'text-slate-500',   'bgLight' => 'bg-slate-50',   'border' => 'border-slate-200',   'label' => 'CERRADA',  'pulse' => false],
                            'cancelled' => ['bg' => 'bg-red-400',     'text' => 'text-red-500',     'bgLight' => 'bg-red-50',     'border' => 'border-red-200',     'label' => 'CANCELADA','pulse' => false],
                        ];
                        $sc = $statusConfig[$auction->status] ?? $statusConfig['pending'];
                        $currentPrice = $auction->current_price > 0 ? $auction->current_price : $auction->start_price;
                        $bidsCount = $auction->bids()->count();
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="py-4 px-4 align-top">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center flex-shrink-0 text-amber-400">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm leading-tight">{{ $auction->generator->model ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $auction->generator->internal_folio ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 align-top">
                            <div class="inline-flex items-center gap-1.5 {{ $sc['bgLight'] }} {{ $sc['border'] }} border px-2 py-1 rounded-md">
                                @if($sc['pulse'])
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $sc['bg'] }} opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $sc['bg'] }}"></span>
                                </span>
                                @else
                                <span class="h-2 w-2 rounded-full {{ $sc['bg'] }}"></span>
                                @endif
                                <span class="text-[10px] font-black {{ $sc['text'] }} tracking-widest">{{ $sc['label'] }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 align-top">
                            <div class="text-sm font-black text-slate-900">${{ number_format($currentPrice, 2) }}</div>
                            <div class="text-xs text-slate-400"><i class="fas fa-gavel text-amber-500 mr-1"></i>{{ $bidsCount }} puja(s)</div>
                        </td>
                        <td class="py-4 px-4 align-top">
                            @if($auction->status === 'active')
                                <div class="text-xs text-orange-600 font-bold mb-1" data-end="{{ $auction->end_time->timestamp }}" data-countdown>
                                    <i class="fas fa-hourglass-half animate-pulse mr-1"></i><span class="countdown-display">Calculando...</span>
                                </div>
                                <div class="text-[10px] text-slate-400">Fin: {{ $auction->end_time->format('d/m/Y H:i') }}</div>
                            @elseif($auction->status === 'pending')
                                <div class="text-xs text-amber-600 font-bold mb-1">
                                    <i class="fas fa-clock mr-1"></i>Inicia: {{ $auction->start_time->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <div class="text-xs text-slate-500 font-bold mb-1">
                                    <i class="fas fa-flag-checkered mr-1"></i>Finalizó: {{ $auction->end_time->format('d/m/Y H:i') }}
                                </div>
                                @if($auction->winner)
                                <div class="text-[10px] text-emerald-600 font-semibold"><i class="fas fa-trophy text-amber-500 mr-1"></i> {{ $auction->winner->name }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="py-4 px-4 align-top text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.auctions.show', $auction) }}"
                                   class="px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-xs font-bold transition-colors">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                
                                @if($auction->status === 'pending' || $auction->status === 'active')
                                <form action="{{ route('admin.auctions.cancel', $auction) }}" method="POST" onsubmit="return confirm('¿Cancelar esta subasta?');" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-bold transition-colors">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </form>
                                @endif

                                @if($auction->status === 'cancelled' || ($auction->status === 'finished' && !$auction->winner))
                                <form action="{{ route('admin.auctions.destroy', $auction) }}" method="POST" onsubmit="return confirm('¿Eliminar permanentemente esta subasta?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white hover:bg-red-700 rounded-lg text-xs font-bold transition-colors shadow-sm">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-gavel text-2xl text-slate-300"></i>
                            </div>
                            <h3 class="text-slate-700 font-bold text-base mb-1">Sin subastas</h3>
                            <p class="text-slate-400 text-sm mb-4">Aún no hay subastas para mostrar en esta vista.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document.ready(function() {
    $('#auctionsTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        ordering: false, // Ya vienen ordenadas del servidor (active, pending, etc)
        pageLength: 25,
    });
});
document.querySelectorAll('[data-countdown]').forEach(el => {
    const endTs = parseInt(el.dataset.end) * 1000;
    const display = el.querySelector('.countdown-display');
    function update() {
        const diff = endTs - Date.now();
        if (diff <= 0) { display.textContent = '¡Subasta cerrada!'; display.classList.add('text-red-600'); return; }
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        display.textContent = (h > 0 ? `${h}h ` : '') + `${String(m).padStart(2,'0')}m ${String(s).padStart(2,'0')}s restantes`;
        setTimeout(update, 1000);
    }
    update();
});
</script>
@endsection
