@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    
    <div class="mb-8 flex items-center gap-4">
        <div class="bg-orange-500 rounded-lg p-3 text-white shadow-lg shadow-orange-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-bold bg-clip-text dark:from-white dark:to-slate-300">
                Escáner de Revisión
            </h1>
            <p class="text-slate-500 mt-1">Busque el generador por folio o número de serie</p>
        </div>
    </div>

    <!-- Buscador -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 p-6 mb-8 transition-all hover:shadow-md">
        <form action="{{ route('operations.revisions.scan') }}" id="search-folio-form" method="GET">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Folio Interno o Número de Serie
            </label>
            <div class="flex gap-3">
                <input type="text" 
                       name="folio" 
                       value="{{ $folio ?? '' }}"
                       placeholder="Ej. FUO-00123"
                       class="flex-1 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 px-4 py-3 placeholder:text-slate-400 transition-colors"
                       required autofocus>
                
                <button type="submit" class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-6 py-3 rounded-xl font-medium hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors flex items-center gap-2" title="Buscar Manualmente">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Buscar
                </button>
                <button type="button" onclick="startQRScanner()" class="bg-orange-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-orange-600 shadow-md shadow-orange-500/20 transition-all flex items-center gap-2" title="Escanear Código QR de Etiqueta">
                    <i class="fas fa-qrcode text-lg"></i> <span class="hidden sm:inline">Escanear QR</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Modal del Escáner QR -->
    <div id="qrModal" class="fixed inset-0 bg-slate-900/80 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl w-full max-w-md shadow-2xl border border-slate-100 dark:border-slate-700">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-qrcode text-orange-500"></i> Escanear Etiqueta
                </h3>
                <button onclick="stopQRScanner()" class="text-slate-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50 dark:hover:bg-red-900/20">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div id="qr-reader" class="rounded-2xl overflow-hidden border-2 border-slate-100 dark:border-slate-700 w-full"></div>
            <p class="text-xs text-center text-slate-500 mt-4 tracking-wide">Apunte la cámara hacia el código QR impreso en la etiqueta de Fuo System.</p>
        </div>
    </div>

    @if($folio)
        @if(!$generator)
            <div class="bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-4 rounded-xl border border-red-200 dark:border-red-900/50 flex gap-3 mb-8">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p>No se encontró ningún generador con el folio o serie: <strong>{{ $folio }}</strong></p>
            </div>
        @else
            <!-- Ficha del Generador -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700/50 flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                {{ $generator->model }}
                            </span>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg 
                                @if($generator->status == 'En revisión') bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400
                                @elseif($generator->status == 'Recibido en almacén') bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400
                                @else bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-400 @endif">
                                {{ $generator->status }}
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-2">{{ $generator->internal_folio }}</h2>
                        <p class="text-sm text-slate-500 mt-1">S/N: {{ $generator->serial_number }}</p>
                    </div>
                </div>

                @if($generator->status !== 'En revisión' && $generator->status !== 'Recibido en almacén')
                    <div class="p-6 bg-orange-50 dark:bg-orange-900/10 border-t border-orange-200 dark:border-orange-900/30">
                        <div class="flex items-start gap-3 text-orange-700 dark:text-orange-400">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <h3 class="font-semibold text-sm">Estado Inesperado</h3>
                                <p class="text-sm mt-1">Este generador está registrado como <strong>"{{ $generator->status }}"</strong>. Usualmente solo los equipos "En revisión" o recién recibidos deben ser inspeccionados.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Formulario de Checklist -->
            @if($checklist && ($generator->status === 'En revisión' || $generator->status === 'Recibido en almacén'))
                <form action="{{ route('operations.revisions.store') }}" method="POST" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 p-6">
                    @csrf
                    <input type="hidden" name="generator_id" value="{{ $generator->id }}">
                    
                    <div class="mb-6 pb-6 border-b border-slate-200 dark:border-slate-700/50">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $checklist->title }}</h3>
                        <p class="text-sm text-slate-500 mt-1">Realice la inspección técnica usando la plantilla activa.</p>
                    </div>

                    <div class="space-y-4 mb-8">
                        @foreach($checklist->items as $index => $item)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-slate-100 dark:border-slate-700/30 bg-slate-50/50 dark:bg-slate-900/20 hover:border-orange-500/30 transition-colors group">
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $item }}</span>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                                    <input type="radio" name="checklist_results[{{ $index }}][status]" value="ok" required class="text-green-500 focus:ring-green-500 w-4 h-4">
                                    <span class="text-sm font-medium">OK</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
                                    <input type="radio" name="checklist_results[{{ $index }}][status]" value="fail" required class="text-red-500 focus:ring-red-500 w-4 h-4">
                                    <span class="text-sm font-medium">Falla</span>
                                </label>
                                <input type="hidden" name="checklist_results[{{ $index }}][item]" value="{{ $item }}">
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"> Observaciones </label>
                        <textarea name="observations" rows="3" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 px-4 py-3 text-slate-900 dark:text-white placeholder:text-slate-400" placeholder="Añada detalles adicionales sobre la revisión"></textarea>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3"> Resultado Final de la Inspección </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative flex flex-col items-center justify-center p-4 cursor-pointer rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all [&:has(input:checked)]:border-green-500 [&:has(input:checked)]:bg-green-50 dark:[&:has(input:checked)]:bg-green-900/20 group">
                                <input type="radio" name="result" value="Aprobada" required class="absolute opacity-0">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 group-[&:has(input:checked)]:bg-green-100 group-[&:has(input:checked)]:text-green-600 dark:group-[&:has(input:checked)]:bg-green-900/50 dark:group-[&:has(input:checked)]:text-green-400 flex items-center justify-center mb-2 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="font-medium text-slate-900 dark:text-white">Aprobada</span>
                                <span class="text-xs text-slate-500 text-center mt-1">Pasa a Lista para envío</span>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 cursor-pointer rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all [&:has(input:checked)]:border-red-500 [&:has(input:checked)]:bg-red-50 dark:[&:has(input:checked)]:bg-red-900/20 group">
                                <input type="radio" name="result" value="No Aprobada" required class="absolute opacity-0">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 group-[&:has(input:checked)]:bg-red-100 group-[&:has(input:checked)]:text-red-600 dark:group-[&:has(input:checked)]:bg-red-900/50 dark:group-[&:has(input:checked)]:text-red-400 flex items-center justify-center mb-2 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                                <span class="font-medium text-slate-900 dark:text-white">Fallida</span>
                                <span class="text-xs text-slate-500 text-center mt-1">Pasa a En taller</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-200 dark:border-slate-700/50">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-xl shadow-lg shadow-orange-500/30 transition-all">
                            Guardar Revisión
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-slate-50 dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 text-center">
                    <p class="text-slate-500 dark:text-slate-400">No hay una plantilla de checklist activa en el sistema.</p>
                </div>
            @endif

        @endif
    @endif
</div>

<!-- Script del Escáner QR -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrcodeScanner;
    
    function startQRScanner() {
        document.getElementById('qrModal').classList.remove('hidden');
        
        html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", 
            { fps: 10, qrbox: {width: 250, height: 250} },
            false
        );
        
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }
    
    function stopQRScanner() {
        if(html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }
        document.getElementById('qrModal').classList.add('hidden');
    }

    function onScanSuccess(decodedText, decodedResult) {
        
        // El QR es el Folio - Ej. LOTE-35-003
        
        let folio = decodedText;
        // if(folio.includes('/')) {
        //     const parts = folio.split('/');
        //     folio = parts[parts.length - 1]; // Extrae el ID puro al final de la URL
        // }
        
        document.querySelector('input[name="folio"]').value = folio;
        stopQRScanner();
        document.querySelector('#search-folio-form').submit(); // Autoconsulta
    }

    function onScanFailure(error) {
        // Omite errores de lectura de frames en tiempo real para no saturar la consola
    }
</script>
@endsection
