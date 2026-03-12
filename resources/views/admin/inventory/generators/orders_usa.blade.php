@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Pedidos Internacionales USA</h1>
            <p class="text-slate-500 font-medium">Registro y seguimiento de nuevas unidades en tránsito desde EE.UU.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.generators.index') }}" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver al Inventario
            </a>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-file-import text-blue-600"></i> Importar
            </button>
            <button onclick="openCreateModal()" class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
                <i class="fas fa-plus text-orange-500"></i> Nuevo Pedido USA
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl text-sm font-bold">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabla de Pedidos Pendientes -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Folio / Serie</th>
                        <th class="px-6 py-4">Modelo del Generador</th>
                        <th class="px-6 py-4">Costo Estimado</th>
                        <th class="px-6 py-4">Fecha de Registro</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($generators as $generator)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-900 tracking-tighter">{{ $generator->internal_folio }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $generator->serial_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-700 uppercase">
                            {{ $generator->model }}
                        </td>
                        <td class="px-6 py-4 font-bold text-orange-600">
                            $ {{ number_format($generator->cost, 2) }} <span class="text-[10px] text-slate-400">MXN</span>
                        </td>
                        <td class="px-6 py-4 text-[10px] text-slate-500 font-medium">
                            {{ $generator->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick='openEditModal({!! $generator->toJson() !!})' class="p-2 text-slate-400 hover:text-blue-600 bg-white border border-slate-100 rounded-lg shadow-sm" title="Editar">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <form action="{{ route('admin.orders.usa.destroy', $generator) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este pedido?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 bg-white border border-slate-100 rounded-lg shadow-sm" title="Eliminar">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-ship text-4xl text-slate-200 mb-4"></i>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No hay pedidos internacionales pendientes</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Pedidos USA -->
<div id="modal-order" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 id="modal-title" class="text-xl font-bold text-slate-900 uppercase tracking-tight">Nuevo Pedido USA</h3>
                    <p id="modal-desc" class="text-slate-500 text-sm">Registra una unidad que viene en tránsito.</p>
                </div>
                <button onclick="closeModal('modal-order')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form id="order-form" action="{{ route('admin.orders.usa.store') }}" method="POST">
                @csrf
                <div id="method-container"></div>
                
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Modelo</label>
                        <input type="text" name="model" id="field-model" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Ej. FG3000">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Número de Serie</label>
                        <input type="text" name="serial_number" id="field-serial_number" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Ej. SN-USA-001">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Folio Interno</label>
                        <input type="text" name="internal_folio" id="field-internal_folio" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Ej. FUO-USA-001">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Costo (MXN)</label>
                        <input type="number" step="0.01" name="cost" id="field-cost" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="0.00">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modal-order')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                    <button type="submit" class="flex-1 bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                        <i class="fas fa-save"></i> <span id="btn-text">Guardar Pedido</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Importar CSV -->
<div id="importModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 uppercase tracking-tight">Importar Generadores</h3>
                    <p class="text-slate-500 text-sm">Sube tu archivo CSV con el inventario.</p>
                </div>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <div class="mb-4 text-xs text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <p class="font-bold mb-2 uppercase text-[10px] tracking-widest text-slate-400">Formato requerido (Columnas exactas):</p>
                <ol class="list-decimal list-inside space-y-1">
                    <li><span class="font-bold text-slate-700">Folio-Interno</span> <span class="text-slate-400">(Vacío = Autogenerado)</span></li>
                    <li><span class="font-bold text-slate-700">No. Serie</span> <span class="text-slate-400">(Vacío = N/A)</span></li>
                    <li><span class="font-bold text-emerald-600">Modelo</span> <span class="text-emerald-500 font-bold">(Obligatorio)</span></li>
                    <li><span class="font-bold text-slate-700">Costo MXN</span> <span class="text-slate-400">(Opcional, numérico)</span></li>
                </ol>
            </div>

            <form action="{{ route('inventory.generators.import.excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-6">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Archivo (.csv)</label>
                    <input type="file" name="file" accept=".csv, .txt" required 
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                        <i class="fas fa-cloud-upload-alt"></i> Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    const modal = document.getElementById('modal-order');
    const form = document.getElementById('order-form');
    const title = document.getElementById('modal-title');
    const desc = document.getElementById('modal-desc');
    const btnText = document.getElementById('btn-text');
    const methodContainer = document.getElementById('method-container');
    
    // Inputs
    const inputModel = document.getElementById('field-model');
    const inputSerial = document.getElementById('field-serial_number');
    const inputFolio = document.getElementById('field-internal_folio');
    const inputCost = document.getElementById('field-cost');

    function openCreateModal() {
        form.action = "{{ route('admin.orders.usa.store') }}";
        methodContainer.innerHTML = '';
        title.innerText = "Nuevo Pedido USA";
        desc.innerText = "Registra una unidad que viene en tránsito.";
        btnText.innerText = "Guardar Pedido";
        
        inputModel.value = '';
        inputSerial.value = '';
        inputFolio.value = '';
        inputCost.value = '';
        
        modal.classList.remove('hidden');
    }

    function openEditModal(generator) {
        form.action = `/admin/orders/usa/${generator.id}`;
        methodContainer.innerHTML = '@method("PUT")';
        title.innerText = "Editar Pedido";
        desc.innerText = "Modifica los datos del pedido en tránsito.";
        btnText.innerText = "Actualizar Pedido";
        
        inputModel.value = generator.model;
        inputSerial.value = generator.serial_number;
        inputFolio.value = generator.internal_folio;
        inputCost.value = generator.cost;
        
        modal.classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection
