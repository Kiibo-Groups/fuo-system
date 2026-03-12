@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">

    {{-- Mensajes --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3 font-bold text-sm">
        <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-bold">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}
    </div>
    @endif

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Control de Envíos y Logística</h1>
            <p class="text-slate-500 font-medium">Selecciona generadores, forma un lote y genera una sola guía de envío.</p>
        </div>
        <div class="flex gap-3 items-center">
            <div id="batch-toolbar" class="hidden items-center gap-3">
                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">
                    <span id="selected-count">0</span> seleccionados
                </span>
                <button onclick="openBatchModal()"
                    class="inline-flex items-center gap-2 bg-slate-900 hover:bg-orange-600 text-white font-black text-xs uppercase tracking-widest px-5 py-2.5 rounded-xl shadow-lg transition-all">
                    <i class="fas fa-truck-loading"></i> Despachar Lote
                </button>
            </div>
            <div class="bg-white px-4 py-2 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Listos para Envío</span>
                <span class="bg-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg">{{ $readyToShip->count() }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        <!-- ===== COLUMNA IZQUIERDA: Unidades Listas ===== -->
        <div class="xl:col-span-1 space-y-4">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 uppercase tracking-tight text-sm">
                        <i class="fas fa-box-open mr-2 text-orange-500"></i> Listos para Salida
                    </h3>
                    <!-- Select All -->
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" id="selectAllUnits"
                            class="w-4 h-4 rounded border-slate-300 text-orange-500 focus:ring-orange-500 cursor-pointer"
                            onchange="toggleSelectAll(this)">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-slate-700 transition-colors">Todos</span>
                    </label>
                </div>

                <div class="p-3 space-y-2 max-h-[70vh] overflow-y-auto">
                    @forelse($readyToShip as $unit)
                    <label for="unit-{{ $unit->id }}" class="unit-card flex items-start gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:border-orange-200 cursor-pointer transition-all group has-[:checked]:border-orange-400 has-[:checked]:bg-orange-50 has-[:checked]:shadow-md">
                        <input type="checkbox" id="unit-{{ $unit->id }}" value="{{ $unit->id }}"
                            class="unit-checkbox mt-0.5 w-4 h-4 rounded border-slate-300 text-orange-500 focus:ring-orange-500 cursor-pointer"
                            onchange="updateSelection()">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $unit->internal_folio }}</span>
                                <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-2 py-0.5 rounded uppercase">Lista</span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800 uppercase mt-1">{{ $unit->model }}</h4>
                            <div class="flex items-center gap-1.5 text-[10px] text-slate-500 mt-1">
                                <i class="fas fa-building text-emerald-500 text-[8px]"></i>
                                <span class="font-bold text-emerald-700 uppercase truncate">{{ $unit->assignedBranch->name ?? 'Sin asignar' }}</span>
                            </div>
                            <div class="text-[9px] text-slate-400 font-mono mt-0.5">S/N: {{ $unit->serial_number }}</div>
                        </div>
                    </label>
                    @empty
                    <div class="text-center py-12">
                        <i class="fas fa-check-double text-3xl text-emerald-200 mb-3"></i>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">No hay unidades pendientes</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ===== COLUMNA DERECHA: Lotes en Tránsito ===== -->
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 uppercase tracking-tight text-sm">
                        <i class="fas fa-truck-moving mr-2 text-blue-500"></i> Lotes en Tránsito
                    </h3>
                    <span class="bg-blue-100 text-blue-700 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">
                        {{ $activeBatches->count() }} lotes
                    </span>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($activeBatches as $batch)
                    @php
                        $batchGenerators = $batch->shipments->map(fn($s) => $s->generator)->filter();
                        $destinations = $batchGenerators->map(fn($g) => optional($g->assignedBranch)->name)->unique()->filter()->values();
                    @endphp
                    <div class="p-5 hover:bg-slate-50/60 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <!-- Lote Header -->
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="bg-slate-900 text-white text-[9px] font-black px-2.5 py-1 rounded-lg uppercase tracking-widest">
                                        Lote #{{ $batch->id }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                                        <span class="text-[9px] font-bold text-blue-600 uppercase">En Tránsito</span>
                                    </div>
                                    <span class="text-[9px] text-slate-400">{{ $batch->created_at->diffForHumans() }}</span>
                                </div>

                                <!-- Paquetería y Guía -->
                                <div class="flex items-center gap-4 mb-3">
                                    <div>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">Paquetería</p>
                                        <p class="text-xs font-bold text-slate-700">{{ $batch->shipping_company }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">Guía</p>
                                        <p class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $batch->tracking_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">Unidades</p>
                                        <p class="text-xs font-bold text-slate-900">{{ $batchGenerators->count() }} piezas</p>
                                    </div>
                                </div>

                                <!-- Destinos -->
                                @if($destinations->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    @foreach($destinations as $dest)
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-100 text-[9px] font-black px-2 py-0.5 rounded-full uppercase">
                                        <i class="fas fa-building text-[7px]"></i> {{ $dest }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif

                                <!-- Lista de folios -->
                                <div class="flex flex-wrap gap-1">
                                    @foreach($batchGenerators as $gen)
                                    <span class="text-[9px] font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded">{{ $gen->internal_folio }}</span>
                                    @endforeach
                                </div>

                                @if($batch->notes)
                                <p class="text-[10px] text-slate-400 italic mt-2">{{ $batch->notes }}</p>
                                @endif
                            </div>

                            <!-- Evidencia + acciones -->
                            <div class="flex flex-col items-end gap-2 shrink-0">
                                @if($batch->evidences && count($batch->evidences) > 0)
                                <a href="{{ Storage::url($batch->evidences[0]) }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-lg text-[10px] font-bold transition-colors">
                                    <i class="fas fa-image"></i> {{ count($batch->evidences) }} foto(s)
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-16">
                        <i class="fas fa-truck text-4xl text-slate-100 mb-4"></i>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">No hay lotes en tránsito</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Despachar Lote ===== -->
<div id="modal-batch-shipment" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-slate-800">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-black text-white uppercase tracking-tight">Despachar Lote</h3>
                    <p class="text-[10px] text-slate-400 mt-1">
                        <span id="batch-unit-count" class="font-bold text-orange-400">0</span> generadores seleccionados
                    </p>
                </div>
                <button onclick="closeBatchModal()" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Resumen de unidades seleccionadas -->
        <div id="batch-summary" class="px-6 py-3 bg-slate-50 border-b border-slate-100 max-h-32 overflow-y-auto">
            <!-- Se llena por JS -->
        </div>

        <form action="{{ route('logistics.shipments.send-batch') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="generator_ids" id="batch-generator-ids">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Paquetería</label>
                    <select name="shipping_company" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none appearance-none">
                        <option value="Castores">Castores</option>
                        <option value="Tres Guerras">Tres Guerras</option>
                        <option value="FedEx">FedEx</option>
                        <option value="DHL">DHL</option>
                        <option value="Privado">Transporte Privado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Número de Guía</label>
                    <input type="text" name="tracking_number" required placeholder="N/A o Folio"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Observaciones del Lote (Opcional)</label>
                <input type="text" name="notes" placeholder="Ej. Contenedor C-4412, Tarima 3..."
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-orange-500 outline-none text-slate-700">
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Evidencia Fotográfica (Opcional)</label>
                <div id="upload-container" class="border-2 border-dashed border-slate-200 rounded-2xl p-5 text-center hover:border-orange-300 transition-colors cursor-pointer relative">
                    <input type="file" name="evidences[]" multiple accept="image/*"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateImageStatus(this)">
                    <div id="upload-content" class="flex flex-col items-center justify-center pointer-events-none">
                        <i id="upload-icon" class="fas fa-camera text-slate-300 text-2xl mb-2 transition-colors"></i>
                        <p id="upload-text" class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Click para subir foto(s) del lote</p>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeBatchModal()"
                    class="flex-1 py-3 text-xs font-black text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase border border-slate-200">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 py-3 text-xs font-black text-white bg-slate-900 hover:bg-orange-600 rounded-xl shadow-lg transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="fas fa-truck-loading"></i> Confirmar Despacho
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let selectedIds = new Set();

    function updateSelection() {
        const checkboxes = document.querySelectorAll('.unit-checkbox');
        selectedIds = new Set();
        checkboxes.forEach(cb => {
            if (cb.checked) selectedIds.add(cb.value);
        });

        const count = selectedIds.size;
        const toolbar = document.getElementById('batch-toolbar');
        document.getElementById('selected-count').innerText = count;

        if (count > 0) {
            toolbar.classList.remove('hidden');
            toolbar.classList.add('flex');
        } else {
            toolbar.classList.add('hidden');
            toolbar.classList.remove('flex');
        }

        // Sync select-all checkbox
        const selectAll = document.getElementById('selectAllUnits');
        selectAll.checked = count > 0 && count === checkboxes.length;
        selectAll.indeterminate = count > 0 && count < checkboxes.length;
    }

    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.unit-checkbox');
        checkboxes.forEach(cb => { cb.checked = source.checked; });
        updateSelection();
    }

    function openBatchModal() {
        if (selectedIds.size === 0) return;

        // Rellenar input hidden
        document.getElementById('batch-generator-ids').value = Array.from(selectedIds).join(',');
        document.getElementById('batch-unit-count').innerText = selectedIds.size;

        // Construir resumen visual
        const summary = document.getElementById('batch-summary');
        const labels = document.querySelectorAll('.unit-card');
        let html = '<div class="flex flex-wrap gap-1.5 py-2">';
        labels.forEach(label => {
            const cb = label.querySelector('.unit-checkbox');
            if (cb && cb.checked) {
                const folio = label.querySelector('.font-mono')?.innerText ?? cb.value;
                const model = label.querySelector('h4')?.innerText ?? '';
                html += `<span class="inline-flex items-center gap-1 bg-orange-50 border border-orange-200 text-orange-700 text-[9px] font-black px-2.5 py-1 rounded-lg uppercase">
                    <i class="fas fa-microchip text-[7px]"></i> ${folio}
                </span>`;
            }
        });
        html += '</div>';
        summary.innerHTML = html;

        document.getElementById('modal-batch-shipment').classList.remove('hidden');
    }

    function closeBatchModal() {
        document.getElementById('modal-batch-shipment').classList.add('hidden');
    }

    function updateImageStatus(input) {
        const textEl = document.getElementById('upload-text');
        const iconEl = document.getElementById('upload-icon');
        const container = document.getElementById('upload-container');

        if (input.files && input.files.length > 0) {
            textEl.innerText = `${input.files.length} archivo(s) seleccionado(s)`;
            textEl.classList.replace('text-slate-400', 'text-orange-500');
            iconEl.className = 'fas fa-check-circle text-orange-500 text-2xl mb-2 transition-colors';
            container.classList.add('border-orange-400', 'bg-orange-50');
            container.classList.remove('border-slate-200');
        } else {
            textEl.innerText = 'Click para subir foto(s) del lote';
            textEl.classList.replace('text-orange-500', 'text-slate-400');
            iconEl.className = 'fas fa-camera text-slate-300 text-2xl mb-2 transition-colors';
            container.classList.remove('border-orange-400', 'bg-orange-50');
            container.classList.add('border-slate-200');
        }
    }

    // Cerrar modal con click fuera
    document.getElementById('modal-batch-shipment').addEventListener('click', function(e) {
        if (e.target === this) closeBatchModal();
    });
</script>
@endsection