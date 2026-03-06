@extends('layouts.app')

@section('content')
    <!-- Bienvenida y Acciones Rápidas -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">¡Hola de nuevo, {{ Auth::user()->name }}!</h1>
            <p class="text-slate-500">Aquí está el resumen operativo de hoy.</p>
        </div>
        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'owner')
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('inventory.generators.export.excel') }}"
                class="bg-white border text-center border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-file-export text-slate-400"></i> Excel
            </a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('admin.orders.usa') }}"
                class="bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-plus text-orange-500"></i> Nuevo Pedido EE.UU.
            </a>
            @endif
        </div>
        @elseif(Auth::user()->role === 'technician')
        <div class="flex gap-3">
            <a href="{{ route('operations.revisions.scan') }}"
                class="bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all flex items-center gap-2">
                <i class="fas fa-qrcode text-orange-500"></i> Escanear Generador
            </a>
        </div>
        @endif
    </div>

    <!-- Banners Publicitarios -->
    @if(isset($activeBanners) && $activeBanners->count() > 0)
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <div class="mb-8 rounded-3xl overflow-hidden shadow-lg border border-slate-100">
        <div class="swiper myBannersSwiper w-full h-48 md:h-72 bg-slate-200">
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
            const swiper = new Swiper('.myBannersSwiper', {
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

    <!-- Grid de Métricas (KPIs) - Solo admin u owner -->
    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'owner')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @if(Auth::user()->role === 'owner')
        <!-- Card Asignados -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="bg-indigo-50 text-indigo-600 w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                <i class="fas fa-boxes"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Asignados</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($assignedCount) }}</h3>
            </div>
        </div>
        @endif

        <!-- Card En Tránsito -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="bg-blue-50 text-blue-600 w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                <i class="fas fa-ship"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">En Tránsito</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($inTransitCount) }}</h3>
            </div>
        </div>

        <!-- Card En Taller / Revisión -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="bg-orange-50 text-orange-600 w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                <i class="fas fa-tools"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">En Taller / Revisión</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($inWorkshopCount) }}</h3>
            </div>
        </div>

        <!-- Card Disponibles Venta -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="bg-emerald-50 text-emerald-600 w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Disponibles Venta</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($availableCount) }}</h3>
            </div>
        </div>

        @if(Auth::user()->role === 'admin')
        <!-- Card Refacciones Bajas -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4 border-l-4 border-l-red-500">
            <div class="bg-red-50 text-red-600 w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Refacciones Bajas</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($lowStockPartsCount) }}</h3>
            </div>
        </div>
        @endif
    </div>
    @endif

    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'owner')
    <!-- Tabla de Inventario Reciente -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">Inventario Reciente y Estados</h3>
            <div class="flex gap-2">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" placeholder="Buscar folio o serie..."
                        class="pl-8 pr-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 w-64">
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Folio / Modelo</th>
                        <th class="px-6 py-4">Sucursal</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Costo Refacciones</th>
                        <th class="px-6 py-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($recentGenerators as $gen)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $gen->internal_folio }}</div>
                                <div class="text-xs text-slate-500">{{ $gen->model }} ({{"S/N: " . $gen->serial_number}})</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-700 px-2 py-1 rounded-md text-xs font-medium">
                                    <i class="fas fa-map-marker-alt text-[10px]"></i> {{ $gen->branch ? $gen->branch->name : 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'Disponible' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'En revisión' => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'En taller' => 'bg-red-100 text-red-700 border-red-200',
                                        'Pedido en tránsito' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'Enviado' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                        'Separado' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'Vendido' => 'bg-purple-100 text-purple-700 border-purple-200',
                                        'Recibido en almacén' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
                                        'Lista para envío' => 'bg-teal-100 text-teal-700 border-teal-200',
                                    ];
                                    $colorClass = $statusColors[$gen->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $colorClass }}">
                                    {{ $gen->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-600">
                                @php
                                    $repairCost = $gen->workshopLogs->max('total_repair_cost') ?? 0;
                                @endphp
                                ${{ number_format($repairCost, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('inventory.generators.show', $gen) }}" class="text-slate-400 hover:text-slate-900 transition-colors">
                                    <i class="fas fa-ellipsis-h"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <i class="fas fa-boxes text-3xl mb-3 text-slate-300"></i>
                                <p>No hay registro de inventario todavía.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-slate-50/50 border-t border-slate-50 text-center">
            <a href="{{ route('inventory.generators.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700 uppercase tracking-widest">Ver Todo
                el Inventario <i class="fas fa-chevron-right ml-1"></i></a>
        </div>
    </div>
    @endif
@endsection