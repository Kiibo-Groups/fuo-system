@php
    $statusConfig = [
        'active'    => ['border' => 'border-red-400',   'badgeBg' => 'bg-red-50 border-red-200',   'badgeText' => 'text-red-600',   'label' => 'EN VIVO',     'pulse' => true],
        'pending'   => ['border' => 'border-amber-400', 'badgeBg' => 'bg-amber-50 border-amber-200','badgeText' => 'text-amber-600', 'label' => 'PRÓXIMA',    'pulse' => false],
        'finished'  => ['border' => 'border-slate-200', 'badgeBg' => 'bg-slate-50 border-slate-200','badgeText' => 'text-slate-400', 'label' => 'CERRADA',    'pulse' => false],
        'cancelled' => ['border' => 'border-slate-200', 'badgeBg' => 'bg-slate-50 border-slate-200','badgeText' => 'text-slate-400', 'label' => 'CANCELADA',  'pulse' => false],
    ];
    $sc = $statusConfig[$auction->status] ?? $statusConfig['pending'];
    $currentPrice = $auction->current_price > 0 ? $auction->current_price : $auction->start_price;
    $bidsCount = $auction->bids()->count();
@endphp

<a href="{{ route('store.auctions.show', $auction) }}"
   class="group block bg-white rounded-3xl border-2 {{ $sc['border'] }} shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">

    {{-- Color band --}}
    <div class="h-1.5 {{ $auction->status === 'active' ? 'bg-gradient-to-r from-red-400 to-red-500' : ($auction->status === 'pending' ? 'bg-gradient-to-r from-amber-400 to-orange-400' : 'bg-slate-200') }}"></div>

    {{-- Generator image/placeholder --}}
    @if($auction->generator->image && Storage::disk('public')->exists($auction->generator->image))
    <div class="h-40 overflow-hidden">
        <img src="{{ Storage::url($auction->generator->image) }}" alt="{{ $auction->generator->model }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
    </div>
    @else
    <div class="h-40 bg-slate-900 flex items-center justify-center overflow-hidden">
        <img src="{{ asset('assets/images/icon.jpeg') }}" alt="Default" class="h-24 w-24 object-contain opacity-50 group-hover:scale-110 group-hover:opacity-75 transition-all duration-500">
    </div>
    @endif

    <div class="p-5">
        {{-- Badge + title --}}
        <div class="flex items-start justify-between gap-2 mb-3">
            <div>
                <h3 class="font-bold text-slate-900 text-sm leading-tight">{{ $auction->generator->model }}</h3>
                <p class="text-xs text-slate-400 font-mono">{{ $auction->generator->internal_folio }}</p>
            </div>
            <div class="flex items-center gap-1.5 {{ $sc['badgeBg'] }} border {{ $sc['badgeBg'] }} px-2 py-1 rounded-full flex-shrink-0">
                @if($sc['pulse'])
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-red-500"></span>
                </span>
                @else
                <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                @endif
                <span class="text-[9px] font-black {{ $sc['badgeText'] }} tracking-widest">{{ $sc['label'] }}</span>
            </div>
        </div>

        {{-- Price --}}
        <div class="bg-slate-50 rounded-2xl px-4 py-3 mb-3">
            <div class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Puja Actual</div>
            <div class="text-xl font-black text-slate-900">${{ number_format($currentPrice, 2) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Base: ${{ number_format($auction->start_price, 2) }}</div>
        </div>

        {{-- Countdown / meta --}}
        <div class="flex items-center justify-between text-xs text-slate-500">
            @if($auction->status === 'active')
            <span data-end="{{ $auction->end_time->timestamp }}" class="flex items-center gap-1.5 text-orange-600 font-semibold">
                <i class="fas fa-hourglass-half animate-pulse text-[10px]"></i>
                <span class="cd-text">...</span>
            </span>
            @elseif($auction->status === 'pending')
            <span class="flex items-center gap-1.5 text-amber-600 font-semibold">
                <i class="fas fa-clock text-[10px]"></i>
                {{ $auction->start_time->format('d/m H:i') }}
            </span>
            @else
            <span class="text-slate-400">{{ $auction->end_time->format('d/m/Y') }}</span>
            @endif
            <span class="flex items-center gap-1 text-slate-400">
                <i class="fas fa-gavel text-[10px]"></i> {{ $bidsCount }}
            </span>
        </div>
    </div>

    {{-- CTA --}}
    @if($auction->status === 'active')
    <div class="px-5 pb-4">
        <div class="w-full text-center bg-gradient-to-r from-amber-400 to-orange-500 text-slate-900 font-black text-xs py-2.5 rounded-xl">
            ¡Pujar ahora! <i class="fas fa-arrow-right ml-1"></i>
        </div>
    </div>
    @elseif($auction->status === 'pending')
    <div class="px-5 pb-4">
        <div class="w-full text-center bg-slate-100 text-slate-500 font-bold text-xs py-2.5 rounded-xl">
            Ver detalles <i class="fas fa-arrow-right ml-1"></i>
        </div>
    </div>
    @endif
</a>
