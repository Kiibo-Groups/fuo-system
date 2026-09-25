@extends('layouts.app')

@section('content')
{{-- Premium Auction Room --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

.auction-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}
.price-glow {
    text-shadow: 0 0 40px rgba(251, 191, 36, 0.5), 0 0 80px rgba(251, 191, 36, 0.2);
}
.pulse-ring {
    animation: pulseRing 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
}
@keyframes pulseRing {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(1.5); opacity: 0; }
}
.bid-enter {
    animation: bidSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes bidSlideIn {
    from { transform: translateY(-12px) scale(0.97); opacity: 0; }
    to   { transform: translateY(0) scale(1); opacity: 1; }
}
.price-bump {
    animation: priceBump 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes priceBump {
    0%  { transform: scale(1); }
    50% { transform: scale(1.08); color: #fbbf24; }
    100%{ transform: scale(1); }
}
.countdown-urgent {
    animation: urgentPulse 1s ease-in-out infinite;
}
@keyframes urgentPulse {
    0%, 100% { color: #ef4444; }
    50% { color: #fca5a5; }
}
.glow-button {
    box-shadow: 0 0 20px rgba(251,191,36,0.3), 0 4px 15px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}
.glow-button:hover {
    box-shadow: 0 0 35px rgba(251,191,36,0.5), 0 8px 25px rgba(0,0,0,0.3);
    transform: translateY(-2px);
}
.bid-input:focus {
    box-shadow: 0 0 0 3px rgba(251,191,36,0.3);
}
</style>

<div class="min-h-screen">

    {{-- ============ HERO SECTION ============ --}}
    <div class="auction-hero text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-slate-400 text-sm mb-6">
                <a href="{{ route('store.available') }}" class="hover:text-white transition-colors">Catálogo</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-amber-400 font-semibold">Subasta en Vivo</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-start">

                {{-- LEFT: Generator Info --}}
                <div>
                    {{-- Live Badge --}}
                    @if($auction->status === 'active')
                    <div class="inline-flex items-center gap-2 bg-red-500/20 border border-red-500/40 px-3 py-1.5 rounded-full mb-4">
                        <div class="relative flex h-2.5 w-2.5">
                            <span class="pulse-ring absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        </div>
                        <span class="text-red-400 text-xs font-black uppercase tracking-widest">EN VIVO</span>
                    </div>
                    @elseif($auction->status === 'finished')
                    <div class="inline-flex items-center gap-2 bg-slate-500/20 border border-slate-500/40 px-3 py-1.5 rounded-full mb-4">
                        <i class="fas fa-flag-checkered text-slate-400 text-xs"></i>
                        <span class="text-slate-400 text-xs font-black uppercase tracking-widest">SUBASTA CERRADA</span>
                    </div>
                    @endif

                    {{-- Generator Details --}}
                    <h1 class="text-3xl lg:text-4xl font-black mb-2 leading-tight">
                        {{ $auction->generator->model }}
                    </h1>
                    <p class="text-slate-400 font-mono text-sm mb-5">
                        Folio: {{ $auction->generator->internal_folio }} &nbsp;·&nbsp; S/N: {{ $auction->generator->serial_number }}
                    </p>

                    {{-- Generator Media Gallery --}}
                    @if($auction->assets && $auction->assets->count() > 0)
                    <div class="mb-5 border border-slate-700 rounded-3xl overflow-hidden shadow-2xl shadow-black/40">
                        <div class="swiper main-swiper bg-slate-900 h-64 lg:h-96 w-full relative">
                            <div class="swiper-wrapper">
                                @foreach($auction->assets as $asset)
                                <div class="swiper-slide flex items-center justify-center">
                                    @if($asset->type === 'image')
                                    <img src="{{ Storage::url($asset->file_path) }}" class="w-full h-full object-cover">
                                    @elseif($asset->type === 'video')
                                    <video src="{{ Storage::url($asset->file_path) }}" controls class="w-full h-full object-contain bg-black"></video>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            <div class="swiper-button-next !text-white !w-10 !h-10 bg-black/30 rounded-full backdrop-blur-md border border-white/10 hover:bg-black/50 transition-colors after:!text-sm"></div>
                            <div class="swiper-button-prev !text-white !w-10 !h-10 bg-black/30 rounded-full backdrop-blur-md border border-white/10 hover:bg-black/50 transition-colors after:!text-sm"></div>
                            <div class="swiper-pagination !bottom-2"></div>
                        </div>
                        @if($auction->assets->count() > 1)
                        <div class="swiper thumb-swiper h-24 bg-slate-800 border-t border-slate-700">
                            <div class="swiper-wrapper">
                                @foreach($auction->assets as $asset)
                                <div class="swiper-slide cursor-pointer opacity-40 [&.swiper-slide-thumb-active]:opacity-100 transition-opacity border-r border-slate-700 last:border-0 relative">
                                    @if($asset->type === 'image')
                                    <img src="{{ Storage::url($asset->file_path) }}" class="w-full h-full object-cover">
                                    @else
                                    <div class="w-full h-full bg-slate-900 flex items-center justify-center relative overflow-hidden">
                                        <div class="absolute inset-0 bg-black/30 z-10"></div>
                                        <i class="fas fa-play text-white/70 text-xl z-20"></i>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @elseif($auction->generator->image && Storage::disk('public')->exists($auction->generator->image))
                    <div class="rounded-3xl overflow-hidden mb-5 shadow-2xl shadow-black/40 border border-slate-700">
                        <img src="{{ Storage::url($auction->generator->image) }}" alt="{{ $auction->generator->model }}"
                             class="w-full h-64 lg:h-96 object-cover">
                    </div>
                    @else
                    <div class="rounded-3xl bg-slate-900 border border-slate-700 h-56 flex flex-col items-center justify-center mb-5 shadow-2xl shadow-black/40 overflow-hidden">
                        <img src="{{ asset('assets/images/icon.jpeg') }}" alt="Default" class="h-32 w-32 object-contain opacity-40">
                    </div>
                    @endif

                    {{-- Specs chips --}}
                    <div class="flex flex-wrap gap-2">
                        <span class="flex items-center gap-1.5 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-full text-xs text-slate-300">
                            <i class="fas fa-map-marker-alt text-amber-400 text-[10px]"></i>
                            {{ $auction->generator->assignedBranch ? $auction->generator->assignedBranch->name : 'Sin sucursal' }}
                        </span>
                        <span class="flex items-center gap-1.5 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-full text-xs text-slate-300">
                            <i class="fas fa-info-circle text-amber-400 text-[10px]"></i>
                            {{ $auction->generator->status }}
                        </span>
                        <span class="flex items-center gap-1.5 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-full text-xs text-slate-300">
                            <i class="fas fa-gavel text-amber-400 text-[10px]"></i>
                            <span id="bids-count">{{ $auction->bids()->count() }}</span> puja(s)
                        </span>
                    </div>
                </div>

                {{-- RIGHT: Bid Box --}}
                <div class="lg:sticky lg:top-8">

                    {{-- Countdown --}}
                    @if($auction->status === 'active')
                    <div id="countdown-box" class="bg-slate-800/60 backdrop-blur-sm border border-slate-700 rounded-3xl p-5 mb-4">
                        <div class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-hourglass-half text-amber-400"></i> Tiempo Restante
                        </div>
                        <div class="grid grid-cols-4 gap-2" id="countdown-grid">
                            @foreach(['dias' => 'DÍAS', 'horas' => 'HRS', 'minutos' => 'MIN', 'segundos' => 'SEG'] as $k => $label)
                            <div class="bg-slate-900 rounded-2xl p-3 text-center border border-slate-700">
                                <div id="cd-{{ $k }}" class="text-2xl lg:text-3xl font-black text-amber-400 tabular-nums">00</div>
                                <div class="text-[9px] font-black uppercase tracking-widest text-slate-500 mt-0.5">{{ $label }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Main Bid Card --}}
                    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl shadow-black/20">

                        {{-- Current Price --}}
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-center relative overflow-hidden">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(251,191,36,0.15),transparent_70%)]"></div>
                            <div class="relative z-10">
                                <div class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1">Puja Actual</div>
                                <div id="current-price" class="text-4xl lg:text-5xl font-black text-amber-400 price-glow">
                                    ${{ number_format($auction->current_price > 0 ? $auction->current_price : $auction->start_price, 2) }}
                                </div>
                                <div class="text-slate-500 text-xs mt-2">
                                    Precio base: ${{ number_format($auction->start_price, 2) }} ·
                                    Mínimo por puja: ${{ number_format($auction->min_increment, 2) }}
                                </div>
                                @if($auction->status === 'active')
                                <div class="mt-3 inline-flex items-center gap-1.5 text-xs text-emerald-400">
                                    <i class="fas fa-arrow-up"></i>
                                    Próxima puja mínima: <strong id="min-bid">${{ number_format(($auction->current_price > 0 ? $auction->current_price : $auction->start_price) + $auction->min_increment, 2) }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="p-5">

                            @if($auction->status === 'active')
                            {{-- Bid Form --}}
                            @auth
                            @if($isRegistered)
                            <form id="bidForm">
                                @csrf
                                <div class="flex gap-2 mb-3">
                                    <div class="relative flex-1">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">$</span>
                                        <input type="number" id="bidAmount" name="amount"
                                               step="0.01"
                                               min="{{ ($auction->current_price > 0 ? $auction->current_price : $auction->start_price) + $auction->min_increment }}"
                                               placeholder="{{ number_format(($auction->current_price > 0 ? $auction->current_price : $auction->start_price) + $auction->min_increment, 2) }}"
                                               class="bid-input w-full pl-9 pr-4 py-3.5 border-2 text-black border-slate-200 rounded-2xl text-lg font-bold focus:outline-none focus:border-amber-400 transition-colors">
                                    </div>
                                    <button type="submit" id="bidBtn"
                                            class="glow-button bg-gradient-to-r from-amber-400 to-orange-500 text-slate-900 font-black px-6 py-3.5 rounded-2xl text-sm flex-shrink-0 flex items-center gap-2">
                                        <i class="fas fa-gavel"></i> ¡Pujar!
                                    </button>
                                </div>

                                {{-- Proxy Bidding Toggle --}}
                                <div class="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between">
                                    <div>
                                        <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5"><i class="fas fa-robot text-indigo-500"></i> Auto-Puja (Proxy Bidding)</label>
                                        <p class="text-[10px] text-slate-500">Definir un tope y pujaremos por ti.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="isProxyBid" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-500"></div>
                                    </label>
                                </div>
                                <div id="currentMaxBidInfo" class="mb-3 text-[11px] text-indigo-600 font-semibold {{ $myMaxBid ? '' : 'hidden' }}">
                                    Tu puja máxima activa: ${{ number_format($myMaxBid ?? 0, 2) }}
                                </div>

                                {{-- Quick bid buttons --}}
                                @php
                                    $baseForQuick = $auction->current_price > 0 ? $auction->current_price : $auction->start_price;
                                    $inc = $auction->min_increment;
                                @endphp
                                <div class="grid grid-cols-3 gap-2 mb-3">
                                    @foreach([1, 2, 5] as $multiplier)
                                    <button type="button" data-multiplier="{{ $multiplier }}" data-amount="{{ $baseForQuick + ($inc * $multiplier) }}"
                                            class="quick-bid bg-slate-50 border border-slate-200 rounded-xl py-2 text-xs font-bold text-slate-700 hover:bg-amber-50 hover:border-amber-300 transition-all">
                                        +${{ number_format($inc * $multiplier, 0) }}
                                        <div class="text-[10px] text-slate-400 font-normal">${{ number_format($baseForQuick + ($inc * $multiplier), 0) }}</div>
                                    </button>
                                    @endforeach
                                </div>

                                {{-- Feedback message --}}
                                <div id="bidFeedback" class="hidden text-xs px-3 py-2.5 rounded-xl text-center font-semibold mb-3"></div>
                            </form>
                            @else
                            <div class="text-center py-4 bg-slate-50 border border-slate-200 rounded-2xl p-5">
                                <i class="fas fa-lock text-3xl text-slate-400 mb-3"></i>
                                <h3 class="font-bold text-slate-800 mb-1">Garantía de Seriedad</h3>
                                <p class="text-xs text-slate-500 mb-4">Para pujar, debes inscribirte realizando una retención temporal (hold) de <strong>${{ number_format($auction->guarantee_amount, 2) }} MXN</strong> en tu tarjeta. Si no ganas, se libera automáticamente.</p>
                                <form action="{{ route('store.auctions.register', $auction) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="glow-button bg-[#009ee3] text-white font-bold px-6 py-3 rounded-xl text-sm w-full">
                                        Pagar Hold con MercadoPago
                                    </button>
                                </form>
                            </div>
                            @endif
                            @else
                            <div class="text-center py-4">
                                <p class="text-slate-500 text-sm mb-3">Debes iniciar sesión para pujar.</p>
                                <a href="{{ route('login') }}" class="glow-button inline-block bg-gradient-to-r from-amber-400 to-orange-500 text-slate-900 font-black px-6 py-2.5 rounded-xl text-sm">
                                    Iniciar Sesión
                                </a>
                            </div>
                            @endauth

                            @elseif($auction->status === 'finished')
                            <div class="text-center py-4">
                                @if($auction->winner)
                                <div class="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-trophy text-3xl text-amber-400"></i>
                                </div>
                                <p class="font-black text-slate-900 text-lg mb-1">¡Subasta Finalizada!</p>
                                <p class="text-slate-500 text-sm">Ganador: <strong class="text-slate-800">{{ $auction->winner->name }}</strong></p>
                                <p class="text-slate-400 text-xs mt-1">Con una puja de ${{ number_format($auction->current_price, 2) }}</p>
                                @else
                                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-times text-3xl text-slate-300"></i>
                                </div>
                                <p class="font-bold text-slate-700">Subasta finalizada sin pujas</p>
                                @endif
                            </div>
                            @else
                            <div class="text-center py-4">
                                <div class="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-clock text-3xl text-amber-400"></i>
                                </div>
                                <p class="font-bold text-slate-800 mb-1">Próximamente</p>
                                <p class="text-slate-500 text-sm">Inicia el {{ $auction->start_time->format('d/m/Y \a \l\a\s H:i') }}</p>
                            </div>
                            @endif

                            {{-- Meta info --}}
                            <div class="border-t border-slate-100 pt-4 mt-2 space-y-2">
                                <div class="flex justify-between text-xs text-slate-500">
                                    <span><i class="fas fa-calendar-alt mr-1"></i>Fin de subasta</span>
                                    <span class="font-semibold text-slate-700">{{ $auction->end_time->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($auction->payment_deadline)
                                <div class="flex justify-between text-xs text-slate-500">
                                    <span><i class="fas fa-credit-card mr-1"></i>Plazo de pago</span>
                                    <span class="font-semibold text-slate-700">{{ $auction->payment_deadline->format('d/m/Y H:i') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ BIDS HISTORY ============ --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Bid History --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-history text-amber-500 text-sm"></i>
                            Historial de Pujas
                        </h3>
                        <span class="text-xs text-slate-400" id="bids-live-label">
                            @if($auction->status === 'active')
                            <span class="flex items-center gap-1.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Actualización en vivo
                            </span>
                            @endif
                        </span>
                    </div>
                    <div id="bids-container" class="divide-y divide-slate-50">
                        @forelse($auction->bids()->with('user')->latest()->take(30)->get() as $i => $bid)
                        <div class="flex items-center justify-between px-5 py-3.5 {{ $i === 0 ? 'bg-amber-50/50' : '' }}">
                            <div class="flex items-center gap-3">
                                @if($i === 0)
                                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                                    <i class="fas fa-crown text-xs"></i>
                                </div>
                                @else
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 flex-shrink-0 text-sm font-bold">
                                    {{ $i + 1 }}
                                </div>
                                @endif
                                <div>
                                    <div class="text-sm font-bold text-slate-800">
                                        {{ $bid->user->name }}
                                        @if($bid->is_auto)
                                            <span class="ml-2 text-[9px] bg-slate-200 text-slate-500 px-1.5 py-0.5 rounded uppercase font-bold"><i class="fas fa-robot"></i> Auto-Puja</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-400">{{ $bid->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-slate-900 {{ $i === 0 ? 'text-amber-600' : '' }}">${{ number_format($bid->amount, 2) }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <i class="fas fa-gavel text-4xl text-slate-200 mb-3"></i>
                            <p class="text-slate-400 text-sm">Sé el primero en pujar</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Info Panel --}}
            <div class="space-y-5">

                {{-- How to bid --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5">
                    <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-sm">
                        <i class="fas fa-question-circle text-amber-500"></i> ¿Cómo pujar?
                    </h4>
                    <ol class="space-y-3">
                        @foreach([
                            ['fas fa-user-check', 'amber', 'Inicia sesión en tu cuenta'],
                            ['fas fa-dollar-sign', 'emerald', 'Ingresa una cantidad mayor a la puja mínima'],
                            ['fas fa-gavel', 'blue', 'Haz clic en ¡Pujar! para registrar tu oferta'],
                            ['fas fa-trophy', 'purple', 'El mayor postor al cerrar gana y recibe su reserva'],
                        ] as $i => [$icon, $color, $text])
                        <li class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-{{ $color }}-50 text-{{ $color }}-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="{{ $icon }} text-xs"></i>
                            </div>
                            <p class="text-xs text-slate-600">{{ $text }}</p>
                        </li>
                        @endforeach
                    </ol>
                </div>

                {{-- Rules --}}
                <div class="bg-slate-900 rounded-3xl p-5 text-white">
                    <h4 class="font-bold mb-3 text-sm flex items-center gap-2">
                        <i class="fas fa-scroll text-amber-400 text-xs"></i> Reglas
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex gap-2"><i class="fas fa-dot-circle text-amber-500 mt-0.5 text-[10px] flex-shrink-0"></i>Incremento mínimo: <strong class="text-white">${{ number_format($auction->min_increment, 2) }}</strong></li>
                        <li class="flex gap-2"><i class="fas fa-dot-circle text-amber-500 mt-0.5 text-[10px] flex-shrink-0"></i>Las pujas son vinculantes y definitivas.</li>
                        <li class="flex gap-2"><i class="fas fa-dot-circle text-amber-500 mt-0.5 text-[10px] flex-shrink-0"></i>El ganador deberá completar el pago antes de {{ $auction->payment_deadline ? $auction->payment_deadline->format('d/m/Y H:i') : 'la fecha límite' }}.</li>
                        <li class="flex gap-2"><i class="fas fa-dot-circle text-amber-500 mt-0.5 text-[10px] flex-shrink-0"></i>El no pago en plazo cancela la reserva.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ SCRIPTS ============ --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
// ---- GALLERY SWIPER ----
if (document.querySelector('.main-swiper')) {
    let thumbSwiper = null;
    if (document.querySelector('.thumb-swiper')) {
        thumbSwiper = new Swiper('.thumb-swiper', {
            spaceBetween: 0,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true,
            breakpoints: { 640: { slidesPerView: 5 }, 1024: { slidesPerView: 6 } }
        });
    }

    const mainSwiper = new Swiper('.main-swiper', {
        spaceBetween: 0,
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        pagination: { el: '.swiper-pagination', clickable: true },
        thumbs: thumbSwiper ? { swiper: thumbSwiper } : {},
        on: {
            slideChange: function () {
                document.querySelectorAll('.main-swiper video').forEach(v => v.pause());
            }
        }
    });
}
const AUCTION_ID   = {{ $auction->id }};
const AUCTION_STATUS = '{{ $auction->status }}';
let END_TIME_TS  = {{ $auction->end_time->timestamp }};
const MIN_INC      = {{ $auction->min_increment }};

// ---- COUNTDOWN ----
function updateCountdown() {
    const diff = END_TIME_TS * 1000 - Date.now();
    if (AUCTION_STATUS !== 'active') return;
    if (diff <= 0) {
        ['dias','horas','minutos','segundos'].forEach(k => {
            const el = document.getElementById('cd-' + k);
            if (el) { el.textContent = '00'; el.classList.add('countdown-urgent'); }
        });
        const btn = document.getElementById('bidBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-times"></i> Tiempo agotado';
            btn.classList.replace('from-amber-400', 'from-slate-400');
            btn.classList.replace('to-orange-500', 'to-slate-500');
        }
        return;
    }
    const d = Math.floor(diff / 86400000);
    const h = Math.floor((diff % 86400000) / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    const vals = { dias: d, horas: h, minutos: m, segundos: s };
    Object.entries(vals).forEach(([k, v]) => {
        const el = document.getElementById('cd-' + k);
        if (!el) return;
        el.textContent = String(v).padStart(2, '0');
        if (diff < 60000) el.classList.add('countdown-urgent');
        else el.classList.remove('countdown-urgent');
    });
    setTimeout(updateCountdown, 1000);
}
if (AUCTION_STATUS === 'active') updateCountdown();

// ---- QUICK BID BUTTONS ----
document.querySelectorAll('.quick-bid').forEach(btn => {
    btn.addEventListener('click', () => {
        const amountInput = document.getElementById('bidAmount');
        if (amountInput) amountInput.value = parseFloat(btn.dataset.amount).toFixed(2);
    });
});

// ---- BID FORM ----
const isProxyToggle = document.getElementById('isProxyBid');
const bidBtnObj = document.getElementById('bidBtn');
if (isProxyToggle && bidBtnObj) {
    isProxyToggle.addEventListener('change', (e) => {
        if (e.target.checked) {
            bidBtnObj.innerHTML = '<i class="fas fa-robot"></i> Fijar Tope';
            bidBtnObj.classList.replace('from-amber-400', 'from-indigo-500');
            bidBtnObj.classList.replace('to-orange-500', 'to-purple-600');
        } else {
            bidBtnObj.innerHTML = '<i class="fas fa-gavel"></i> ¡Pujar!';
            bidBtnObj.classList.replace('from-indigo-500', 'from-amber-400');
            bidBtnObj.classList.replace('to-purple-600', 'to-orange-500');
        }
    });
}

const bidForm = document.getElementById('bidForm');
if (bidForm) {
    bidForm.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = document.getElementById('bidBtn');
        const amount = parseFloat(document.getElementById('bidAmount').value);
        const feedback = document.getElementById('bidFeedback');

        if (!amount || amount <= 0) { showFeedback('error', 'Ingresa un monto válido.'); return; }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...';

        try {
            const isProxy = document.getElementById('isProxyBid') ? document.getElementById('isProxyBid').checked : false;

            const res = await fetch(`/store/auctions/${AUCTION_ID}/bids`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ?
                        document.querySelector('meta[name=csrf-token]').content :
                        '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ amount, is_proxy: isProxy })
            });
            const data = await res.json();
            if (res.ok) {
                showFeedback('success', data.message || '¡Puja registrada con éxito!');
                document.getElementById('bidAmount').value = '';
                if (isProxy) {
                    const infoEl = document.getElementById('currentMaxBidInfo');
                    if(infoEl) {
                        infoEl.textContent = 'Tu puja máxima activa: $' + amount.toLocaleString('es-MX', {minimumFractionDigits:2});
                        infoEl.classList.remove('hidden');
                    }
                }
                // Optimistically update own UI (real update via websocket for others)
                if (data.bid) {
                    const formattedBid = {
                        amount: data.bid.amount,
                        user_name: data.bid.user ? data.bid.user.name : '{{ auth()->user()->name ?? 'Tú' }}',
                        is_auto: data.bid.is_auto
                    };
                    prependBid(formattedBid);
                }
                updatePriceUI(data.current_price, MIN_INC);
            } else {
                showFeedback('error', data.message || 'Error al procesar la puja.');
            }
        } catch(err) {
            showFeedback('error', 'Error de conexión. Intenta de nuevo.');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-gavel mr-1"></i> ¡Pujar!';
    });
}

function showFeedback(type, msg) {
    const el = document.getElementById('bidFeedback');
    if (!el) return;
    el.textContent = msg;
    el.className = type === 'success'
        ? 'text-xs px-3 py-2.5 rounded-xl text-center font-semibold mb-3 bg-emerald-50 text-emerald-700 border border-emerald-200'
        : 'text-xs px-3 py-2.5 rounded-xl text-center font-semibold mb-3 bg-red-50 text-red-700 border border-red-200';
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 5000);
}

function updatePriceUI(currentPrice, minInc) {
    const priceEl = document.getElementById('current-price');
    const minBidEl = document.getElementById('min-bid');
    const countEl = document.getElementById('bids-count');
    const newMin = currentPrice + minInc;

    if (priceEl) {
        priceEl.textContent = '$' + currentPrice.toLocaleString('es-MX', {minimumFractionDigits:2});
        priceEl.classList.add('price-bump');
        priceEl.addEventListener('animationend', () => priceEl.classList.remove('price-bump'), { once: true });
    }
    if (minBidEl) minBidEl.textContent = '$' + newMin.toLocaleString('es-MX', {minimumFractionDigits:2});

    const bidInput = document.getElementById('bidAmount');
    if (bidInput) { bidInput.min = newMin.toFixed(2); bidInput.placeholder = newMin.toFixed(2); }

    document.querySelectorAll('.quick-bid').forEach(btn => {
        const mult = parseInt(btn.dataset.multiplier);
        const newAmt = currentPrice + (minInc * mult);
        btn.dataset.amount = newAmt;
        btn.querySelector('div').textContent = '$' + newAmt.toLocaleString('es-MX', {minimumFractionDigits:0});
    });
}

function prependBid(bid) {
    const container = document.getElementById('bids-container');
    if (!container) return;

    // Remove empty state if present
    const empty = container.querySelector('.text-center.py-12');
    if (empty) empty.remove();

    // Remove crown from old top bid
    container.querySelectorAll('[data-top]').forEach(el => el.removeAttribute('data-top'));

    const autoBadge = bid.is_auto ? '<span class="ml-2 text-[9px] bg-slate-200 text-slate-500 px-1.5 py-0.5 rounded uppercase font-bold"><i class="fas fa-robot"></i> Auto-Puja</span>' : '';

    const row = document.createElement('div');
    row.className = 'bid-enter flex items-center justify-between px-5 py-3.5 bg-amber-50/50';
    row.dataset.top = '1';
    row.innerHTML = `
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                <i class="fas fa-crown text-xs"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-slate-800">${bid.user_name} ${autoBadge}</div>
                <div class="text-xs text-slate-400">Ahora mismo</div>
            </div>
        </div>
        <div class="font-black text-amber-600">$${parseFloat(bid.amount).toLocaleString('es-MX', {minimumFractionDigits:2})}</div>
    `;

    // Remove previous top styling
    const prev = container.firstElementChild;
    if (prev) {
        prev.classList.remove('bg-amber-50/50');
        const prevCrown = prev.querySelector('.fa-crown');
        if (prevCrown) {
            prevCrown.closest('div.w-8').className = 'w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 flex-shrink-0';
            prevCrown.className = 'text-sm font-bold';
            prevCrown.parentElement.outerHTML = `<div class="text-sm font-bold text-slate-500">2</div>`;
        }
    }
    container.prepend(row);

    // Update count
    const countEl = document.getElementById('bids-count');
    if (countEl) countEl.textContent = parseInt(countEl.textContent) + 1;
}

// ---- WEBSOCKETS (Laravel Reverb / Pusher) ----
if (AUCTION_STATUS === 'active') {
    const isPusher = '{{ env("BROADCAST_CONNECTION") }}' === 'pusher';
    
    let pusherConfig = {};
    if (isPusher) {
        pusherConfig = {
            cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}',
            forceTLS: true
        };
    } else {
        pusherConfig = {
            wsHost: '{{ env("REVERB_HOST", "localhost") }}',
            wsPort: {{ env("REVERB_PORT", 8080) }},
            wssPort: {{ env("REVERB_PORT", 8080) }},
            forceTLS: '{{ env("REVERB_SCHEME", "http") }}' === 'https',
            enabledTransports: ['ws', 'wss'],
            cluster: 'mt1', // unused but required
        };
    }

    const pusher = new Pusher(isPusher ? '{{ env("PUSHER_APP_KEY") }}' : '{{ env("REVERB_APP_KEY") }}', pusherConfig);
    const hammerSound = new Audio('https://actions.google.com/sounds/v1/tools/ratchet_wrench.ogg'); // Un sonido provisional gratis

    let soundUnlocked = false;
    document.body.addEventListener('click', () => {
        if (!soundUnlocked) {
            hammerSound.play().then(() => {
                hammerSound.pause();
                hammerSound.currentTime = 0;
                soundUnlocked = true;
            }).catch(() => {});
        }
    }, { once: true });

    const channel = pusher.subscribe('auction.' + AUCTION_ID);
    channel.bind('App\\Events\\BidPlaced', data => {
        if (data.bid) prependBid(data.bid);
        if (data.current_price) updatePriceUI(parseFloat(data.current_price), MIN_INC);
        if (data.end_time) {
            END_TIME_TS = data.end_time;
        }
        if (soundUnlocked) {
            hammerSound.currentTime = 0;
            hammerSound.play().catch(e => console.log('Audio autoplay blocked'));
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
