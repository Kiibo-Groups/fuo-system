@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="bg-blue-500 rounded-lg p-3 text-white shadow-lg shadow-blue-500/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-clip-text dark:from-white dark:to-slate-300">
                    Mis Separaciones
                </h1>
                <p class="text-slate-500 mt-1">Equipos reservados (Válido por 4 horas)</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('store.available') }}" class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-5 py-2.5 rounded-xl font-medium hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al Catálogo
            </a>
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
            <h3 class="font-bold text-slate-800 dark:text-white">Reservaciones Activas</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse border-slate-200 dark:border-slate-700/50">
                <thead class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest border-b border-slate-200 dark:border-slate-700/50">
                    <tr>
                        <th class="px-6 py-4">Equipo Separado</th>
                        <th class="px-6 py-4">Precio de Venta</th>
                        <th class="px-6 py-4">Fecha Expiración</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm">
                    @forelse($reservations as $reservation)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white text-lg">{{ $reservation->generator->model }}</div>
                                <div class="text-xs text-slate-500 mt-1">S/N: {{ $reservation->generator->serial_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700 dark:text-slate-300">
                                    ${{ number_format($reservation->generator->sale_price ?? 0, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400 text-xs font-bold px-3 py-1.5 rounded-full inline-flex items-center gap-1.5 whitespace-nowrap">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Expira: {{ $reservation->expires_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($reservation->generator->status === 'Separado')
                                    <span class="text-green-600 dark:text-green-400 font-bold flex flex-col items-center">
                                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Separación Válida
                                    </span>
                                @else
                                    <span class="text-slate-500">{{ $reservation->generator->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center mx-auto mb-4 border border-slate-200 dark:border-slate-800">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                No tienes ninguna separación de equipo activa en este momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
