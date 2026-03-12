@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Control de Refacciones</h1>
            <p class="text-slate-500 font-medium">Gestión de stock, costos y alertas de reabastecimiento.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.spare-parts.export.excel') }}" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-file-excel text-emerald-600"></i> Exportar Inventario
            </a>
            <button onclick="openCreateModal()" class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
                <i class="fas fa-plus text-orange-500"></i> Nueva Refacción
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

    <!-- Tabla de Refacciones -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Descripción / Nombre</th>
                        <th class="px-6 py-4">Stock Actual</th>
                        <th class="px-6 py-4">Costo Unitario</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($spareParts as $part)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-4 font-mono text-slate-400">#{{ str_pad($part->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 uppercase text-xs">{{ $part->name }}</div>
                            @if($part->stock <= $part->low_stock_threshold)
                                <div class="text-[9px] text-red-500 font-black uppercase tracking-tight flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-exclamation-triangle"></i> Stock Bajo
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $part->stock <= $part->low_stock_threshold ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-600' }}">
                                {{ $part->stock }} unidades
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-700">
                            $ {{ number_format($part->unit_cost, 2) }} <span class="text-[10px] text-slate-400">MXN</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick='openEditModal({!! $part->toJson() !!})' class="p-2 text-slate-400 hover:text-blue-600 bg-white border border-slate-100 rounded-lg shadow-sm" title="Editar">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <form action="{{ route('inventory.spare-parts.destroy', $part) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta refacción?')">
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
                                <i class="fas fa-boxes text-4xl text-slate-200 mb-4"></i>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No hay refacciones registradas</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Refacciones -->
<div id="modal-spare-part" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 id="modal-title" class="text-xl font-bold text-slate-900 uppercase tracking-tight">Nueva Refacción</h3>
                    <p id="modal-desc" class="text-slate-500 text-sm">Gestiona el inventario de piezas y accesorios.</p>
                </div>
                <button onclick="closeModal('modal-spare-part')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form id="spare-part-form" action="{{ route('inventory.spare-parts.store') }}" method="POST">
                @csrf
                <div id="method-container"></div>
                
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Nombre / Descripción</label>
                        <input type="text" name="name" id="field-name" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Ej. Filtro de Aire 500w">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Stock Inicial</label>
                            <input type="number" name="stock" id="field-stock" required 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                                placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Costo Unit. (MXN)</label>
                            <input type="number" step="0.01" name="unit_cost" id="field-unit_cost" required 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                                placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Umbral Stock Bajo</label>
                        <input type="number" name="low_stock_threshold" id="field-low_stock_threshold" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            value="5">
                        <p class="text-[9px] text-slate-400 mt-1 uppercase font-bold">Alertar cuando el stock sea igual o menor a este valor.</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modal-spare-part')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                    <button type="submit" class="flex-1 bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                        <i class="fas fa-save"></i> <span id="btn-text">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modalSP = document.getElementById('modal-spare-part');
    const formSP = document.getElementById('spare-part-form');
    const titleSP = document.getElementById('modal-title');
    const descSP = document.getElementById('modal-desc');
    const btnTextSP = document.getElementById('btn-text');
    const methodContainerSP = document.getElementById('method-container');
    
    // Inputs
    const inputName = document.getElementById('field-name');
    const inputStock = document.getElementById('field-stock');
    const inputUnitCost = document.getElementById('field-unit_cost');
    const inputThreshold = document.getElementById('field-low_stock_threshold');

    function openCreateModal() {
        formSP.action = "{{ route('inventory.spare-parts.store') }}";
        methodContainerSP.innerHTML = '';
        titleSP.innerText = "Nueva Refacción";
        descSP.innerText = "Gestiona el inventario de piezas y accesorios.";
        btnTextSP.innerText = "Guardar";
        
        inputName.value = '';
        inputStock.value = '0';
        inputUnitCost.value = '';
        inputThreshold.value = '5';
        
        modalSP.classList.remove('hidden');
    }

    function openEditModal(part) {
        formSP.action = `/inventory/spare-parts/${part.id}`;
        methodContainerSP.innerHTML = '@method("PUT")';
        titleSP.innerText = "Editar Refacción";
        descSP.innerText = "Actualiza los datos de costo y existencias.";
        btnTextSP.innerText = "Actualizar";
        
        inputName.value = part.name;
        inputStock.value = part.stock;
        inputUnitCost.value = part.unit_cost;
        inputThreshold.value = part.low_stock_threshold;
        
        modalSP.classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection
