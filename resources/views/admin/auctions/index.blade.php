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

    {{-- Auction Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
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

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 overflow-hidden group">

            {{-- Card Top Band --}}
            <div class="h-1.5 bg-gradient-to-r from-amber-400 to-orange-500"></div>

            <div class="p-5">
                {{-- Generator Info --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-bolt text-amber-400"></i>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm leading-tight">{{ $auction->generator->model ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $auction->generator->internal_folio ?? '' }}</div>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="flex items-center gap-1.5 {{ $sc['bgLight'] }} {{ $sc['border'] }} border px-2.5 py-1 rounded-full">
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
                </div>

                {{-- Price Info --}}
                <div class="bg-slate-50 rounded-2xl p-4 mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Puja Actual</span>
                        <span class="text-xs text-slate-400"><i class="fas fa-gavel mr-1 text-amber-500"></i>{{ $bidsCount }} puja{{ $bidsCount !== 1 ? 's' : '' }}</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900">${{ number_format($currentPrice, 2) }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Precio base: ${{ number_format($auction->start_price, 2) }} · Incremento: ${{ number_format($auction->min_increment, 2) }}</div>
                </div>

                {{-- Countdown / Dates --}}
                @if($auction->status === 'active')
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-4"
                     data-end="{{ $auction->end_time->timestamp }}"
                     data-countdown>
                    <i class="fas fa-hourglass-half text-orange-500 animate-pulse"></i>
                    <span class="countdown-display font-semibold text-orange-600">Calculando...</span>
                </div>
                @elseif($auction->status === 'pending')
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
                    <i class="fas fa-calendar-alt text-amber-500"></i>
                    <span>Inicia: {{ $auction->start_time->format('d/m/Y H:i') }}</span>
                </div>
                @else
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
                    <i class="fas fa-flag-checkered text-slate-400"></i>
                    <span>Finalizó: {{ $auction->end_time->format('d/m/Y H:i') }}</span>
                    @if($auction->winner)
                    · <span class="text-emerald-600 font-semibold"><i class="fas fa-trophy text-amber-500"></i> {{ $auction->winner->name }}</span>
                    @endif
                </div>
                @endif

                {{-- Footer Actions --}}
                <div class="flex gap-2">
                    <a href="{{ route('admin.auctions.show', $auction) }}"
                       class="flex-1 text-center bg-slate-900 text-white text-xs font-bold py-2.5 rounded-xl hover:bg-slate-800 transition-colors">
                        Ver Subasta
                    </a>
                    @if($auction->status === 'pending' || $auction->status === 'active')
                    <form action="{{ route('admin.auctions.cancel', $auction) }}" method="POST"
                          onsubmit="return confirm('¿Cancelar esta subasta?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-3 py-2.5 rounded-xl border border-red-200 text-red-500 hover:bg-red-50 text-xs transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-24">
            <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-gavel text-3xl text-slate-300"></i>
            </div>
            <h3 class="text-slate-700 font-bold text-lg mb-1">Sin subastas aún</h3>
            <p class="text-slate-400 text-sm mb-6">Crea la primera subasta para un generador disponible.</p>
            <a href="{{ route('admin.auctions.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-orange-200 hover:scale-105 transition-transform">
                <i class="fas fa-plus"></i> Crear Subasta
            </a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($auctions->hasPages())
    <div class="mt-8">{{ $auctions->links() }}</div>
    @endif
</div>

<script>
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
