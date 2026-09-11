@extends('layouts.app')

@section('content')
<div class="p-6 md:p-8">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-200">
                <i class="fas fa-gavel text-white text-sm"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Subastas en Vivo</h1>
                <p class="text-slate-400 text-sm">Puja por los generadores disponibles y gana al mejor precio.</p>
            </div>
        </div>
    </div>

    {{-- Live Auctions Section --}}
    @php
        $activeAuctions   = $auctions->where('status', 'active');
        $pendingAuctions  = $auctions->where('status', 'pending');
        $finishedAuctions = $auctions->where('status', 'finished');
    @endphp

    {{-- ACTIVE --}}
    @if($activeAuctions->count())
    <div class="mb-10">
        <div class="flex items-center gap-2 mb-4">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
            </span>
            <h2 class="text-sm font-black uppercase tracking-widest text-red-600">En Vivo Ahora</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($activeAuctions as $auction)
                @include('client.auctions._card', ['auction' => $auction])
            @endforeach
        </div>
    </div>
    @endif

    {{-- PENDING --}}
    @if($pendingAuctions->count())
    <div class="mb-10">
        <div class="flex items-center gap-2 mb-4">
            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
            <h2 class="text-sm font-black uppercase tracking-widest text-amber-600">Próximamente</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($pendingAuctions as $auction)
                @include('client.auctions._card', ['auction' => $auction])
            @endforeach
        </div>
    </div>
    @endif

    {{-- FINISHED --}}
    @if($finishedAuctions->count())
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
            <h2 class="text-sm font-black uppercase tracking-widest text-slate-400">Finalizadas Recientemente</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 opacity-70">
            @foreach($finishedAuctions as $auction)
                @include('client.auctions._card', ['auction' => $auction])
            @endforeach
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if($auctions->isEmpty())
    <div class="text-center py-28">
        <div class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-gavel text-4xl text-slate-300"></i>
        </div>
        <h3 class="text-slate-700 font-bold text-xl mb-2">Sin subastas disponibles</h3>
        <p class="text-slate-400 text-sm max-w-xs mx-auto">Cuando el equipo publique una subasta aparecerá aquí. ¡Mantente atento!</p>
    </div>
    @endif
</div>

{{-- Countdown scripts para todas las cards --}}
<script>
document.querySelectorAll('[data-end]').forEach(el => {
    const endTs = parseInt(el.dataset.end) * 1000;
    const display = el.querySelector('.cd-text');
    if (!display) return;
    function tick() {
        const diff = endTs - Date.now();
        if (diff <= 0) { display.textContent = 'Cerrada'; display.classList.add('text-red-500'); return; }
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        display.textContent = (h > 0 ? `${h}h ` : '') + `${String(m).padStart(2,'0')}m ${String(s).padStart(2,'0')}s`;
        setTimeout(tick, 1000);
    }
    tick();
});
</script>
@endsection
