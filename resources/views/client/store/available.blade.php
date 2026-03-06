@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="bg-orange-500 rounded-lg p-3 text-white shadow-lg shadow-orange-500/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-clip-text dark:from-white dark:to-slate-300">
                    Catálogo de Equipos
                </h1>
                <p class="text-slate-500 mt-1">Generadores disponibles en su sucursal</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('store.reservations') }}" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-xl font-medium border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Mis Separaciones
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-900/50 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/50 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Banners Publicitarios -->
    @if(isset($activeBanners) && $activeBanners->count() > 0)
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <div class="mb-8 rounded-2xl overflow-hidden shadow-lg border border-slate-200 dark:border-slate-700/50">
        <div class="swiper myBannersSwiper w-full h-48 md:h-72 bg-slate-200 dark:bg-slate-800">
            <div class="swiper-wrapper">
                @foreach($activeBanners as $banner)
                <div class="swiper-slide relative w-full h-full">
                    @if($banner->target_url)
                        <a href="{{ $banner->target_url }}" target="_blank" class="block w-full h-full">
                    @endif
                        <img src="{{ Storage::url($banner->image_path) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                        @if($banner->title)
                        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-900/80 to-transparent p-4 md:p-6">
                            <h2 class="text-white font-black text-lg md:text-2xl drop-shadow-md">{{ $banner->title }}</h2>
                        </div>
                        @endif
                    @if($banner->target_url)
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next text-white drop-shadow-md after:text-xl"></div>
            <div class="swiper-button-prev text-white drop-shadow-md after:text-xl"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const swiperClient = new Swiper('.myBannersSwiper', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        });
    </script>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 p-6 mb-8 transition-all hover:shadow-md">
        <form action="{{ route('store.available') }}" method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por modelo o serie..." class="flex-1 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 px-4 py-3 placeholder:text-slate-400 transition-colors">
            <button type="submit" class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-6 py-3 rounded-xl font-medium hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors flex items-center gap-2">
                Buscar
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($generators as $generator)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden hover:shadow-lg transition-all group flex flex-col">
                <div class="aspect-video bg-slate-100 dark:bg-slate-900 flex items-center justify-center relative overflow-hidden">
                    <!-- Placeholder de imagen -->
                    <svg class="w-16 h-16 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    
                    <div class="absolute top-3 right-3">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-500 text-white shadow-md">
                            Disponible
                        </span>
                    </div>
                </div>
                
                <div class="p-5 flex-1 flex flex-col">
                    <div class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-mono">S/N: {{ $generator->serial_number }}</div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 line-clamp-2">{{ $generator->model }}</h3>
                    <div class="mt-auto pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-700/50">
                        <div class="font-bold text-xl text-slate-900 dark:text-white">
                            ${{ number_format($generator->cost, 2) }}
                        </div>
                        <button type="button" onclick="openReserveModal({{ $generator->id }}, '{{ $generator->model }}')" class="bg-orange-500 hover:bg-orange-600 text-white rounded-lg p-2 font-medium transition-colors" title="Separar Equipo">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4">
                <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50 border-dashed rounded-3xl p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Sin Resultados</h3>
                    <p class="text-slate-500 max-w-sm mx-auto">Actualmente no hay generadores disponibles que coincidan con su búsqueda.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $generators->links() }}
    </div>

    <!-- Modal Separar -->
    <div id="reserveModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeReserveModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/50 overflow-hidden transform transition-all">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700/50 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">Separar Equipo (24h)</h3>
                <button onclick="closeReserveModal()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="reserveForm" method="POST" action="">
                @csrf
                <div class="p-6">
                    <div class="p-4 bg-orange-50 dark:bg-orange-900/10 text-orange-700 dark:text-orange-400 rounded-xl mb-6 text-sm flex gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>Al confirmar, el equipo <strong><span id="modalGenModel"></span></strong> será separado por <strong>4 horas</strong>. Si no se concreta la venta, será liberado automáticamente.</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Teléfono de Contacto</label>
                        <input type="text" name="client_phone" required placeholder="Ej. 1234567890" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 px-4 py-3">
                    </div>
                </div>
                
                <div class="p-6 border-t border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                    <button type="button" onclick="closeReserveModal()" class="px-5 py-2.5 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-medium shadow-lg shadow-orange-500/30 transition-all">Confirmar Separación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openReserveModal(id, model) {
        document.getElementById('modalGenModel').textContent = model;
        // set form action dynmically
        const form = document.getElementById('reserveForm');
        form.action = `/store/reserve/${id}`;
        document.getElementById('reserveModal').classList.remove('hidden');
    }
    function closeReserveModal() {
        document.getElementById('reserveModal').classList.add('hidden');
    }
</script>
@endsection
