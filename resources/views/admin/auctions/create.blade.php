@extends('layouts.app')

@section('content')
<div class="p-6 md:p-8">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('admin.auctions.index') }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Nueva Subasta</h1>
            <p class="text-slate-400 text-sm">Selecciona un generador y configura las reglas.</p>
        </div>
    </div>

    <form action="{{ route('admin.auctions.store') }}" method="POST" id="auctionForm" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- LEFT: Main Form --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Generator Selector --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-black">1</span>
                        Seleccionar Generador
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                            <input type="text" id="generatorSearch" placeholder="Buscar por folio o modelo..."
                                   class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 bg-slate-50">
                        </div>
                    </div>
                    <div id="generatorGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-1">
                        @foreach($generators as $gen)
                        <label class="generator-card cursor-pointer group" data-folio="{{ strtolower($gen->internal_folio) }}" data-model="{{ strtolower($gen->model) }}">
                            <input type="radio" name="generator_id" value="{{ $gen->id }}" class="sr-only peer" required>
                            <div class="flex items-center gap-3 p-3.5 rounded-2xl border-2 border-slate-100 bg-slate-50 transition-all duration-200
                                        peer-checked:border-amber-400 peer-checked:bg-amber-50 group-hover:border-slate-300">
                                <div class="w-10 h-10 rounded-xl bg-slate-200 peer-checked:bg-amber-200 flex items-center justify-center flex-shrink-0 transition-colors">
                                    <i class="fas fa-bolt text-slate-400 peer-checked:text-amber-600 text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 text-sm truncate">{{ $gen->model }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $gen->internal_folio }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ $gen->assignedBranch ? $gen->assignedBranch->name : 'Sin sucursal' }}
                                    </div>
                                </div>
                                <div class="ml-auto flex-shrink-0">
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-amber-500 peer-checked:bg-amber-500 flex items-center justify-center transition-all">
                                        <i class="fas fa-check text-white text-[8px] opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('generator_id')
                        <p class="text-red-500 text-xs mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror

                    {{-- Branch Selection --}}
                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <label for="branch_id" class="block text-sm font-bold text-slate-700 mb-1">
                            <i class="fas fa-map-marker-alt text-amber-500 mr-1"></i> Sucursal Destino
                        </label>
                        <select name="branch_id" id="branch_id"
                                class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-50 transition-all">
                            <option value="">Todas las sucursales (Global)</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Solo los clientes de esta sucursal (o globales) podrán ver y pujar en esta subasta.</p>
                        @error('branch_id')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-black">2</span>
                        Configuración de Precios
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Precio Base <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input type="number" name="start_price" step="0.01" min="0"
                                       value="{{ old('start_price') }}"
                                       placeholder="0.00"
                                       class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 @error('start_price') border-red-300 @enderror">
                            </div>
                            @error('start_price')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Incremento Mínimo <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input type="number" name="min_increment" step="0.01" min="1"
                                       value="{{ old('min_increment', 100) }}"
                                       class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 @error('min_increment') border-red-300 @enderror">
                            </div>
                            @error('min_increment')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Garantía Seriedad <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input type="number" name="guarantee_amount" step="0.01" min="0"
                                       value="{{ old('guarantee_amount', 5000) }}"
                                       class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 @error('guarantee_amount') border-red-300 @enderror">
                            </div>
                            @error('guarantee_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-black">3</span>
                        Duración y Plazos
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Inicio de Subasta <span class="text-red-400">*</span></label>
                            <input type="datetime-local" name="start_time" value="{{ old('start_time') }}"
                                   class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 @error('start_time') border-red-300 @enderror">
                            @error('start_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Fin de Subasta <span class="text-red-400">*</span></label>
                            <input type="datetime-local" name="end_time" value="{{ old('end_time') }}"
                                   class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 @error('end_time') border-red-300 @enderror">
                            @error('end_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Plazo de Pago <span class="text-red-400">*</span></label>
                            <input type="datetime-local" name="payment_deadline" value="{{ old('payment_deadline') }}"
                                   class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 @error('payment_deadline') border-red-300 @enderror">
                            @error('payment_deadline')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-3"><i class="fas fa-info-circle mr-1"></i>El plazo de pago es el tiempo que tiene el ganador para completar el pago después de que cierre la subasta.</p>
                </div>

                {{-- Multimedia Assets --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-black">4</span>
                        Archivos Multimedia (Fotos / Videos)
                    </h3>
                    <div class="relative border-2 border-dashed border-slate-200 rounded-2xl p-8 hover:bg-slate-50 transition-colors group text-center" id="dropzone">
                        <input type="file" name="assets[]" id="fileInput" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-msvideo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="flex justify-center mb-3">
                            <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-cloud-upload-alt text-amber-600 text-2xl"></i>
                            </div>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700 mb-1">Arrastra tus archivos aquí o haz clic</h4>
                        <p class="text-xs text-slate-400">Máximo 20 MB por archivo. Formatos permitidos: JPG, PNG, WEBP, MP4, MOV.</p>
                    </div>
                    @error('assets.*')
                        <p class="text-red-500 text-xs mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    
                    {{-- Preview List --}}
                    <div id="filePreviewContainer" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mt-4 empty:hidden"></div>
                </div>
            </div>

            {{-- RIGHT: Preview & Submit --}}
            <div class="space-y-5">

                {{-- Live Preview Card --}}
                <div class="bg-slate-900 rounded-3xl p-5 text-white sticky top-6">
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Vista Previa</div>

                    {{-- Preview Generator --}}
                    <div id="preview-generator" class="flex items-center gap-3 mb-5 p-3 bg-slate-800 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                            <i class="fas fa-bolt text-amber-400"></i>
                        </div>
                        <div>
                            <div id="preview-model" class="font-bold text-sm text-slate-200">Ningún generador seleccionado</div>
                            <div id="preview-folio" class="text-xs text-slate-500 font-mono"></div>
                        </div>
                    </div>

                    {{-- Preview Price --}}
                    <div class="mb-5">
                        <div class="text-xs text-slate-400 mb-1">Precio Base</div>
                        <div id="preview-price" class="text-3xl font-black text-amber-400">$0.00</div>
                        <div class="text-xs text-slate-500 mt-1">Incremento mínimo: <span id="preview-increment" class="text-slate-300 font-semibold">$100.00</span></div>
                    </div>

                    {{-- Preview Dates --}}
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400"><i class="fas fa-play-circle mr-1"></i>Inicio</span>
                            <span id="preview-start" class="text-slate-300 font-mono">—</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400"><i class="fas fa-stop-circle mr-1"></i>Cierre</span>
                            <span id="preview-end" class="text-slate-300 font-mono">—</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400"><i class="fas fa-credit-card mr-1"></i>Plazo pago</span>
                            <span id="preview-payment" class="text-slate-300 font-mono">—</span>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn"
                            class="w-full bg-gradient-to-r from-amber-400 to-orange-500 text-slate-900 font-black py-3 rounded-2xl hover:scale-105 transition-transform shadow-lg shadow-orange-900/40 flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-gavel"></i> Publicar Subasta
                    </button>
                </div>

                {{-- Tips Card --}}
                <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5">
                    <div class="text-xs font-black text-amber-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fas fa-lightbulb text-amber-500"></i> Consejos
                    </div>
                    <ul class="space-y-2 text-xs text-amber-800">
                        <li class="flex gap-2"><i class="fas fa-check-circle text-amber-500 mt-0.5 flex-shrink-0"></i>El precio base debe ser competitivo para atraer pujas.</li>
                        <li class="flex gap-2"><i class="fas fa-check-circle text-amber-500 mt-0.5 flex-shrink-0"></i>Incrementos de $100–$500 son ideales para generadores.</li>
                        <li class="flex gap-2"><i class="fas fa-check-circle text-amber-500 mt-0.5 flex-shrink-0"></i>Da al ganador al menos 24h para completar el pago.</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Live preview updates
const fmtDate = v => v ? new Date(v).toLocaleString('es-MX', {dateStyle:'short', timeStyle:'short'}) : '—';
const fmtCurrency = v => isNaN(v) || v === '' ? '$0.00' : '$' + parseFloat(v).toLocaleString('es-MX', {minimumFractionDigits:2});

document.querySelectorAll('[name=generator_id]').forEach(r => {
    r.addEventListener('change', () => {
        const card = r.closest('label');
        document.getElementById('preview-model').textContent = card.querySelector('.font-bold').textContent.trim();
        document.getElementById('preview-folio').textContent = card.querySelector('.font-mono').textContent.trim();
    });
});
document.getElementById('auctionForm').querySelectorAll('input').forEach(inp => {
    inp.addEventListener('input', () => {
        if (inp.name === 'start_price')       document.getElementById('preview-price').textContent = fmtCurrency(inp.value);
        if (inp.name === 'min_increment')     document.getElementById('preview-increment').textContent = fmtCurrency(inp.value);
        if (inp.name === 'start_time')        document.getElementById('preview-start').textContent = fmtDate(inp.value);
        if (inp.name === 'end_time')          document.getElementById('preview-end').textContent = fmtDate(inp.value);
        if (inp.name === 'payment_deadline')  document.getElementById('preview-payment').textContent = fmtDate(inp.value);
    });
});

// Generator search
document.getElementById('generatorSearch').addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.generator-card').forEach(card => {
        const match = card.dataset.folio.includes(q) || card.dataset.model.includes(q);
        card.style.display = match ? '' : 'none';
    });
});
// File Upload Preview
const fileInput = document.getElementById('fileInput');
const filePreviewContainer = document.getElementById('filePreviewContainer');

fileInput.addEventListener('change', function(e) {
    filePreviewContainer.innerHTML = '';
    const files = Array.from(e.target.files);
    
    files.forEach((file, index) => {
        const reader = new FileReader();
        const div = document.createElement('div');
        div.className = 'relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 group';
        
        if (file.type.startsWith('image/')) {
            reader.onload = function(e) {
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="text-white text-[10px] font-bold px-2 truncate w-full text-center">${file.name}</span>
                    </div>
                `;
            }
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            div.innerHTML = `
                <div class="w-full h-full flex items-center justify-center bg-slate-800">
                    <i class="fas fa-video text-slate-400 text-xl"></i>
                </div>
                <div class="absolute inset-x-0 bottom-0 bg-black/60 p-1 backdrop-blur-sm">
                    <div class="text-white text-[9px] font-bold truncate text-center">${file.name}</div>
                </div>
            `;
        }
        
        filePreviewContainer.appendChild(div);
    });
});
</script>
@endsection
