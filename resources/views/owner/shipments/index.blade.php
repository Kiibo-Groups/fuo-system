@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="bg-blue-500 rounded-lg p-3 text-white shadow-lg shadow-blue-500/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-clip-text dark:from-white dark:to-slate-300">
                    Recepción en Sucursal
                </h1>
                <p class="text-slate-500 mt-1">Confirme la llegada de los equipos asignados a su sucursal</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-900/50 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden mb-8">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700/50 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 dark:text-white">Generadores en Camino</h3>
            <span class="bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                {{ $incomingShipments->count() }} Pendientes
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-[10px] uppercase font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Información del Equipo</th>
                        <th class="px-6 py-4">Datos de Envío</th>
                        <th class="px-6 py-4">Evidencia</th>
                        <th class="px-6 py-4text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm">
                    @forelse($incomingShipments as $shipment)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $shipment->generator->internal_folio }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">S/N: {{ $shipment->generator->serial_number }}</div>
                                <span class="inline-block mt-2 px-2.5 py-1 text-[10px] font-semibold rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $shipment->generator->model }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    {{ $shipment->shipping_company }}
                                </div>
                                <div class="text-xs text-slate-500 mt-1">Guía: <span class="font-mono text-slate-900 dark:text-slate-400 font-medium">{{ $shipment->tracking_number }}</span></div>
                                <div class="text-xs text-slate-400 mt-1">Enviado: {{ $shipment->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 flex flex-col gap-2">
                                @if($shipment->evidences && count($shipment->evidences) > 0)
                                    @foreach($shipment->evidences as $index => $evidence)
                                        <a href="{{ Storage::url($evidence) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            Ver Foto {{ count($shipment->evidences) > 1 ? ($index + 1) : '' }}
                                        </a>
                                    @endforeach
                                @elseif($shipment->photo_evidence_path)
                                    <a href="{{ Storage::url($shipment->photo_evidence_path) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Ver Foto
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 italic">No hay evidencia</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('logistics.shipments.receive', $shipment->generator_id) }}" method="POST" onsubmit="return confirm('¿Confirma que ha recibido físicamente este equipo en su sucursal?');">
                                    @csrf
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-lg shadow-blue-500/30 transition-all text-xs inline-flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Confirmar Recepción
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-800 shadow-sm">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                No hay envíos en camino hacia esta sucursal en este momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
