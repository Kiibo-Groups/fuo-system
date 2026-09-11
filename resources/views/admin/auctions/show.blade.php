@extends('layouts.app')

@section('content')
<div class="p-6 md:p-8">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.auctions.index') }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Subasta #{{ $auction->id }}</h1>
            <p class="text-slate-400 text-sm">{{ $auction->generator->model ?? 'N/A' }} · {{ $auction->generator->internal_folio ?? '' }}</p>
        </div>
        @php
            $statusConfig = [
                'active'    => ['bg' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'label' => '● EN VIVO'],
                'pending'   => ['bg' => 'bg-amber-100 text-amber-700 border-amber-200',       'label' => '● PENDIENTE'],
                'finished'  => ['bg' => 'bg-slate-100 text-slate-500 border-slate-200',       'label' => '✓ FINALIZADA'],
                'cancelled' => ['bg' => 'bg-red-100 text-red-500 border-red-200',             'label' => '✕ CANCELADA'],
            ];
            $sc = $statusConfig[$auction->status] ?? $statusConfig['pending'];
        @endphp
        <span class="ml-2 px-3 py-1 rounded-full border text-[11px] font-black {{ $sc['bg'] }} uppercase tracking-widest">{{ $sc['label'] }}</span>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Main Stats --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- KPI Row --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @php
                    $bidsCount = $auction->bids()->count();
                    $currentPrice = $auction->current_price > 0 ? $auction->current_price : $auction->start_price;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                    <div id="current-price" class="text-2xl font-black text-amber-500">${{ number_format($currentPrice, 2) }}</div>
                    <div class="text-xs text-slate-400 font-semibold mt-1">Puja Actual</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                    <div id="bids-count-stat" class="text-2xl font-black text-slate-900">{{ $bidsCount }}</div>
                    <div class="text-xs text-slate-400 font-semibold mt-1">Pujas Totales</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                    <div class="text-2xl font-black text-slate-900">${{ number_format($auction->start_price, 2) }}</div>
                    <div class="text-xs text-slate-400 font-semibold mt-1">Precio Base</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                    <div class="text-2xl font-black text-slate-900">${{ number_format($auction->min_increment, 2) }}</div>
                    <div class="text-xs text-slate-400 font-semibold mt-1">Incremento Mín.</div>
                </div>
            </div>

            {{-- Bid History Table --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-history text-amber-500 text-sm"></i> Historial de Pujas</h3>
                    <span class="text-xs text-slate-400 flex items-center gap-2">
                        <span><span id="bids-count-label">{{ $bidsCount }}</span> puja(s) registradas</span>
                        @if($auction->status === 'active')
                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full text-[10px] font-bold border border-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> LIVE
                        </span>
                        @endif
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold tracking-widest">
                            <tr>
                                <th class="px-5 py-3">#</th>
                                <th class="px-5 py-3">Usuario</th>
                                <th class="px-5 py-3">Monto</th>
                                <th class="px-5 py-3">Fecha/Hora</th>
                            </tr>
                        </thead>
                        <tbody id="bids-table-body" class="divide-y divide-slate-50 text-sm">
                            @forelse($auction->bids()->with('user')->latest()->get() as $i => $bid)
                            <tr class="{{ $i === 0 ? 'bg-amber-50/40' : 'hover:bg-slate-50' }} transition-colors">
                                <td class="px-5 py-3.5">
                                    @if($i === 0)
                                    <i class="fas fa-crown text-amber-400"></i>
                                    @else
                                    <span class="text-slate-400 text-xs">{{ $i + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-slate-800">{{ $bid->user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $bid->user->email }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-black {{ $i === 0 ? 'text-amber-600 text-base' : 'text-slate-700' }}">${{ number_format($bid->amount, 2) }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-500 text-xs font-mono">{{ $bid->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm"><i class="fas fa-gavel text-2xl text-slate-200 mb-2 block"></i>Sin pujas aún</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar Details --}}
        <div class="space-y-5">

            {{-- Winner Card --}}
            @if($auction->winner)
            <div class="bg-gradient-to-br from-amber-400 to-orange-500 rounded-3xl p-5 text-slate-900 shadow-lg shadow-orange-200">
                <div class="text-xs font-black uppercase tracking-widest mb-3 opacity-70">🏆 Ganador</div>
                <div class="font-black text-xl mb-0.5">{{ $auction->winner->name }}</div>
                <div class="text-sm opacity-80 mb-3">{{ $auction->winner->email }}</div>
                <div class="bg-white/30 rounded-xl p-3">
                    <div class="text-xs font-bold opacity-70 mb-0.5">Puja ganadora</div>
                    <div class="text-2xl font-black">${{ number_format($auction->current_price, 2) }}</div>
                </div>
            </div>
            @endif

            {{-- Info Card --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5">
                <h4 class="font-bold text-slate-800 mb-4 text-sm">Detalles de la Subasta</h4>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-400">Generador</dt>
                        <dd class="font-semibold text-slate-800">{{ $auction->generator->model }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-400">Folio</dt>
                        <dd class="font-mono text-slate-800">{{ $auction->generator->internal_folio }}</dd>
                    </div>
                    <div class="border-t border-slate-50 pt-3 flex justify-between text-sm">
                        <dt class="text-slate-400">Inicio</dt>
                        <dd class="text-slate-800 text-xs">{{ $auction->start_time->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-400">Cierre</dt>
                        <dd class="text-slate-800 text-xs">{{ $auction->end_time->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($auction->payment_deadline)
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-400">Plazo pago</dt>
                        <dd class="text-slate-800 text-xs">{{ $auction->payment_deadline->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Public Link --}}
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4">
                <div class="text-xs font-bold text-slate-500 mb-2">Enlace Público</div>
                <div class="flex gap-2">
                    <input type="text" readonly value="{{ route('store.auctions.show', $auction) }}"
                           class="flex-1 text-xs bg-white border border-slate-200 rounded-lg px-3 py-2 text-slate-600 font-mono truncate">
                    <button onclick="navigator.clipboard.writeText('{{ route('store.auctions.show', $auction) }}')"
                            class="bg-slate-900 text-white px-3 py-2 rounded-lg text-xs hover:bg-slate-700 transition-colors flex-shrink-0">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ SCRIPTS ============ --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
const AUCTION_ID   = {{ $auction->id }};
const AUCTION_STATUS = '{{ $auction->status }}';

if (AUCTION_STATUS === 'active') {
    const pusher = new Pusher('{{ env("REVERB_APP_KEY") }}', {
        wsHost: '{{ env("REVERB_HOST", "localhost") }}',
        wsPort: {{ env("REVERB_PORT", 8080) }},
        wssPort: {{ env("REVERB_PORT", 8080) }},
        forceTLS: '{{ env("REVERB_SCHEME", "http") }}' === 'https',
        enabledTransports: ['ws', 'wss'],
        cluster: 'mt1',
    });

    const channel = pusher.subscribe('auction.' + AUCTION_ID);
    channel.bind('App\\Events\\BidPlaced', data => {
        // Update Price
        const priceEl = document.getElementById('current-price');
        if (priceEl && data.current_price) {
            priceEl.textContent = '$' + parseFloat(data.current_price).toLocaleString('es-MX', {minimumFractionDigits:2});
            priceEl.style.transform = 'scale(1.05)';
            setTimeout(() => { priceEl.style.transform = 'scale(1)'; }, 300);
            priceEl.style.transition = 'transform 0.3s';
        }

        // Prepend Bid
        if (data.bid) {
            const tbody = document.getElementById('bids-table-body');
            if (tbody) {
                // Remove empty state
                const emptyTr = tbody.querySelector('td[colspan="4"]');
                if (emptyTr) emptyTr.parentElement.remove();
                
                // Unset crown from old top
                const firstRow = tbody.firstElementChild;
                if (firstRow) {
                    firstRow.classList.remove('bg-amber-50/40');
                    firstRow.classList.add('hover:bg-slate-50');
                    const crown = firstRow.querySelector('.fa-crown');
                    if (crown) crown.outerHTML = '<span class="text-slate-400 text-xs">2</span>';
                    const amt = firstRow.querySelector('.text-amber-600');
                    if (amt) {
                        amt.classList.remove('text-amber-600', 'text-base');
                        amt.classList.add('text-slate-700');
                    }
                }

                // Create new row
                const tr = document.createElement('tr');
                tr.className = 'bg-amber-50/40 transition-colors';
                tr.innerHTML = `
                    <td class="px-5 py-3.5"><i class="fas fa-crown text-amber-400"></i></td>
                    <td class="px-5 py-3.5">
                        <div class="font-semibold text-slate-800">${data.bid.user_name}</div>
                        <div class="text-xs text-slate-400">Actualización en vivo</div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-black text-amber-600 text-base">$${parseFloat(data.bid.amount).toLocaleString('es-MX', {minimumFractionDigits:2})}</span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 text-xs font-mono">Ahora</td>
                `;
                tbody.prepend(tr);
            }
            
            // Update Counts
            const countStat = document.getElementById('bids-count-stat');
            const countLabel = document.getElementById('bids-count-label');
            if (countStat) countStat.textContent = parseInt(countStat.textContent) + 1;
            if (countLabel) countLabel.textContent = parseInt(countLabel.textContent) + 1;
        }
    });

    channel.bind('App\\Events\\AuctionStatusChanged', data => {
        if (data.status !== AUCTION_STATUS) {
            window.location.reload();
        }
    });
}
</script>
@endsection
