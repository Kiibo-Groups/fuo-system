@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-4 md:p-8 bg-slate-50">
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase"><i class="fas fa-list-alt text-blue-600 mr-2"></i> Generador Automático de Listados</h1>
                <p class="text-slate-500 font-medium mt-1">Pega los datos directamente desde tu Excel para generar el reporte formateado.</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 md:p-8">
            
            <form id="generator-form" action="{{ route('admin.list-generator.process') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Porcentaje -->
                    <div>
                        <label for="percentage" class="block text-sm font-bold text-slate-700 mb-2">Porcentaje (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" id="percentage" name="percentage" value="20" required
                                class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all font-medium text-slate-800">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold">%</span>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 font-medium"><i class="fas fa-info-circle"></i> Ej. 20 para 20%</p>
                    </div>

                    <!-- Cambio Dolar -->
                    <div>
                        <label for="exchange_rate" class="block text-sm font-bold text-slate-700 mb-2">Cambio Dólar (DL)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold">$</span>
                            </div>
                            <input type="number" step="0.01" id="exchange_rate" name="exchange_rate" value="18.50" required
                                class="w-full pl-8 pr-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all font-medium text-slate-800">
                        </div>
                        <p class="mt-2 text-xs text-slate-500 font-medium"><i class="fas fa-info-circle"></i> Ej. 18.50</p>
                    </div>

                    <!-- Multiplicador -->
                    <div>
                        <label for="multiplier" class="block text-sm font-bold text-slate-700 mb-2">Multiplicador Fijo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold"><i class="fas fa-times"></i></span>
                            </div>
                            <input type="number" step="0.1" id="multiplier" name="multiplier" value="3.3" required
                                class="w-full pl-8 pr-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all font-medium text-slate-800">
                        </div>
                        <p class="mt-2 text-xs text-slate-500 font-medium"><i class="fas fa-info-circle"></i> Ej. 3.3 o 3.4</p>
                    </div>
                </div>

                <div class="mb-8">
                    <label for="raw_data" class="block text-sm font-bold text-slate-700 mb-2">
                        Pegar listado de Excel:
                    </label>
                    <p class="text-xs text-slate-500 mb-3 font-medium">Asegúrate de que las tres primeras columnas sean: <strong>SKU</strong>, <strong>Descripción</strong>, y <strong>Precio Listado</strong>.</p>
                    
                    <textarea id="raw_data" name="raw_data" rows="12" required
                        class="w-full p-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all font-mono text-sm text-slate-700 bg-slate-50 placeholder-slate-400"
                        placeholder="57336&#9;26 Gallon, 175 PSI Ultra Quiet...&#9;$399.99&#10;58605&#9;20 Amp Plasma Cutter&#9;$349.99"></textarea>
                </div>
                
                <div class="mb-8 p-4 bg-blue-50 border border-blue-100 text-blue-700 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-info-circle text-xl mt-0.5"></i>
                    <div>
                        <p class="font-bold text-sm mb-1">Descarga automática de fotos</p>
                        <p class="text-sm">Si el sistema detecta SKUs nuevos que no tienen imagen, intentará descargar la foto de Harbor Freight automáticamente al procesar. Este proceso puede tardar un par de segundos adicionales por cada producto nuevo.</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="submit-btn" class="bg-blue-600 text-white px-8 py-3.5 rounded-xl font-black shadow-sm hover:bg-blue-700 hover:shadow-md transition-all text-sm flex items-center gap-2">
                        <i class="fas fa-magic" id="btn-icon"></i> <span id="btn-text">Procesar y Generar Listado</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('generator-form').addEventListener('submit', function() {
        const btn = document.getElementById('submit-btn');
        const icon = document.getElementById('btn-icon');
        const text = document.getElementById('btn-text');
        
        // Bloquear el botón para evitar doble clic
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        
        // Cambiar el icono por un spinner animado
        icon.classList.remove('fa-magic');
        icon.classList.add('fa-spinner', 'fa-spin');
        
        // Cambiar el texto
        text.innerText = 'Procesando e Investigando SKUs...';
    });
</script>
@endsection
