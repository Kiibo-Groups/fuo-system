@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="bg-orange-500 rounded-lg p-3 text-white shadow-lg shadow-orange-500/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-clip-text dark:from-white dark:to-slate-300">
                    Taller de Reparación
                </h1>
                <p class="text-slate-500 mt-1">Generadores pendientes de diagnóstico y reparación</p>
            </div>
        </div>
        <div>
            <a href="{{ route('operations.revisions.scan') }}" class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-5 py-2.5 rounded-xl font-medium hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Escanear Generador
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($generators as $generator)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden hover:shadow-md transition-all group flex flex-col">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700/50">
                    <div class="flex items-start justify-between mb-4">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                            {{ $generator->model }}
                        </span>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400 animate-pulse">
                            En reparación
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $generator->internal_folio }}</h2>
                    <p class="text-sm text-slate-500 mt-1">S/N: {{ $generator->serial_number }}</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-900/50 flex-1">
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-3">
                        @php
                            $lastRevision = $generator->revisions()->latest()->first();
                        @endphp
                        @if($lastRevision && $lastRevision->observations)
                            <strong class="font-medium text-slate-900 dark:text-white">Observación de Técnico:</strong><br>
                            {{ $lastRevision->observations }}
                        @else
                            Sin observaciones previas.
                        @endif
                    </p>
                </div>
                <div class="p-6 pt-0 mt-auto bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700/50 flex gap-3">
                    <a href="{{ route('operations.workshop.create', ['generator_id' => $generator->id]) }}" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 px-4 rounded-xl text-center shadow-lg shadow-orange-500/30 transition-all text-sm mt-4">
                        Ingresar Diagnóstico
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3">
                <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50 border-dashed rounded-3xl p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Taller Libre</h3>
                    <p class="text-slate-500 max-w-sm mx-auto">Actualmente no hay generadores esperando reparación en el taller.</p>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection
