@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">

    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="bg-blue-500 rounded-2xl p-3 text-white shadow-lg shadow-blue-500/30">
                <i class="fas fa-boxes text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Recepción en Sucursal</h1>
                <p class="text-slate-500 mt-1 text-sm">Confirme la llegada de los lotes asignados a su sucursal</p>
            </div>
        </div>
        <span class="bg-blue-100 text-blue-700 text-xs font-black px-4 py-2 rounded-full uppercase tracking-wider">
            {{ $incomingBatches->count() }} Lotes Pendientes
        </span>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3 font-bold text-sm">
        <i class="fas fa-check-circle text-emerald-500 text-lg"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Lista de lotes --}}
    @forelse($incomingBatches as $batch)
    @php
        $batchGenerators = $batch->shipments->map(fn($s) => $s->generator)->filter();
        $batchCount = $batchGenerators->count();
    @endphp
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm mb-6 overflow-hidden">

        {{-- Header del lote --}}
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-orange-500 rounded-xl p-2.5 shadow-lg">
                    <i class="fas fa-pallet text-white text-base"></i>
                </div>
                <div>
                    <h3 class="text-white font-black text-base tracking-tight">Lote #{{ $batch->id }}</h3>
                    <p class="text-slate-400 text-[10px] uppercase tracking-widest font-bold mt-0.5">
                        {{ $batch->created_at->format('d/m/Y H:i') }} · {{ $batch->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-orange-400 font-black text-2xl">{{ $batchCount }}</p>
                <p class="text-slate-400 text-[10px] uppercase">piezas</p>
            </div>
        </div>

        {{-- Datos de envío --}}
        <div class="p-5 border-b border-slate-50">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Paquetería</p>
                    <p class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-shipping-fast text-blue-400"></i> {{ $batch->shipping_company }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Número de Guía</p>
                    <p class="font-mono text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-lg inline-block">
                        {{ $batch->tracking_number }}
                    </p>
                </div>
                @if($batch->notes)
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Observaciones</p>
                    <p class="text-sm text-slate-600 font-medium">{{ $batch->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Evidencias --}}
            @if($batch->evidences && count($batch->evidences) > 0)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($batch->evidences as $index => $evidence)
                <a href="{{ Storage::url($evidence) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition-colors">
                    <i class="fas fa-image text-slate-400"></i> Foto {{ count($batch->evidences) > 1 ? ($index + 1) : '' }}
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Generadores del lote --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-5 py-3">Folio Interno</th>
                        <th class="px-5 py-3">Modelo / S.N.</th>
                        <th class="px-5 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($batchGenerators as $gen)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="font-black text-slate-900 tracking-tighter">{{ $gen->internal_folio }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-bold text-slate-700 text-xs uppercase">{{ $gen->model }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $gen->serial_number }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-black px-2.5 py-1 rounded-full uppercase">
                                <i class="fas fa-truck text-[8px]"></i> {{ $gen->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Botón de recepción --}}
        <div class="p-5 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
            <p class="text-xs text-slate-500 font-medium">
                Al confirmar, <span class="font-black text-slate-800">{{ $batchCount }} generadores</span> pasarán a estado <span class="font-black text-emerald-600">Disponible</span> en su inventario.
            </p>
            <form action="{{ route('logistics.shipments.receive-batch', $batch->id) }}" method="POST"
                onsubmit="return confirm('¿Confirma que ha recibido físicamente los {{ $batchCount }} generadores del Lote #{{ $batch->id }}?');">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-widest py-2.5 px-6 rounded-xl shadow-lg shadow-emerald-500/20 transition-all">
                    <i class="fas fa-check-double"></i> Confirmar Recepción del Lote
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-16 text-center">
        <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-6 border border-slate-100 shadow-sm">
            <i class="fas fa-box-open text-3xl text-slate-300"></i>
        </div>
        <h3 class="font-black text-slate-700 text-lg mb-2">Sin Envíos Pendientes</h3>
        <p class="text-slate-400 text-sm">No hay lotes en camino hacia esta sucursal en este momento.</p>
    </div>
    @endforelse

</div>
@endsection
