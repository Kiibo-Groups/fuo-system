@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <!-- Encabezado y Resumen -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Inventario Global</h1>
            <p class="text-slate-500 font-medium">Control y trazabilidad en tiempo real de todos los generadores.</p>
        </div>
        @if(Auth::user()->role === 'admin' )
        <div class="flex items-center gap-2 flex-wrap">
            <button id="btnBatchUpdate" onclick="openBatchModal()" class="hidden bg-orange-600 border border-orange-600 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-sm hover:bg-orange-700 transition-all items-center gap-2">
                <i class="fas fa-layer-group"></i> Lote (<span id="batchCount">0</span>)
            </button>
            <button id="btnBatchDelete" onclick="submitBatchDelete()" class="hidden bg-red-600 border border-red-600 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-sm hover:bg-red-700 transition-all items-center gap-2">
                <i class="fas fa-trash"></i> Eliminar (<span id="batchDeleteCount">0</span>)
            </button>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-file-import text-blue-600"></i> Importar
            </button>
            <a href="{{ route('inventory.generators.export.excel', request()->query()) }}" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-file-excel text-emerald-600"></i> Exportar
            </a>
            <button onclick="openCreateModal()" class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
                <i class="fas fa-plus text-orange-500"></i> Nuevo Generador
            </button>
            <a href="{{ route('admin.orders.usa') }}" class="bg-orange-500 text-slate-900 px-4 py-2 rounded-xl font-bold text-xs shadow-lg shadow-orange-100 hover:bg-orange-400 transition-all flex items-center gap-2">
                <i class="fas fa-ship"></i> Nuevo Pedido USA
            </a>
        </div>
        @endif
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

    <!-- Filtros Avanzados -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-8">
        <form action="{{ route('inventory.generators.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Búsqueda rápida</label>
                <i class="fas fa-search absolute left-3 top-[30px] text-slate-300 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Serie, Folio o Modelo..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-orange-500 outline-none">
            </div>
            @if(Auth::user()->role !== 'owner')
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Filtrar por Sucursal</label>
                <select name="branch_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 outline-none appearance-none cursor-pointer">
                    <option value="">Todas las sedes</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Estado de Máquina</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 outline-none appearance-none cursor-pointer">
                    <option value="">Cualquier estado</option>
                    @foreach(['Pedido en tránsito', 'Recibido en almacén', 'En revisión', 'En taller', 'Lista para envío', 'Enviado', 'Disponible', 'Separado', 'Vendido'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 text-white py-2 rounded-xl font-bold text-xs hover:bg-slate-800 transition-all">
                    Filtrar
                </button>
                <a href="{{ route('inventory.generators.index') }}" class="p-2 bg-slate-100 text-slate-400 hover:text-slate-600 rounded-xl transition-all" title="Limpiar Filtros">
                    <i class="fas fa-undo-alt"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Inventario -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                    <tr>
                        @if(Auth::user()->role === 'admin' )
                        <th class="px-6 py-4 w-12 text-center">
                            <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500 cursor-pointer w-4 h-4" title="Seleccionar todos">
                        </th>
                        @endif
                        <th class="px-6 py-4">Folio / Serie</th>
                        <th class="px-6 py-4">Modelo del Generador</th>
                        <th class="px-6 py-4">Ubicación Actual</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Último Mov.</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($generators as $generator)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        @if(Auth::user()->role === 'admin' )
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" name="selected_generators[]" value="{{ $generator->id }}" class="generator-checkbox rounded border-slate-300 text-orange-500 focus:ring-orange-500 cursor-pointer w-4 h-4">
                        </td>
                        @endif
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-900 tracking-tighter">{{ $generator->internal_folio }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $generator->serial_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-700 uppercase text-xs">{{ $generator->model }}</div>
                            <div class="text-[10px] text-orange-600 font-bold tracking-tight">$ {{ number_format($generator->cost, 2) }} USD</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                                <span class="text-xs font-bold text-slate-600 uppercase">{{ $generator->branch->name ?? 'En tránsito' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClasses = [
                                    'Pedido en tránsito' => 'bg-blue-50 text-blue-600 border-blue-100',
                                    'Recibido en almacén' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    'En revisión' => 'bg-orange-50 text-orange-600 border-orange-200',
                                    'En taller' => 'bg-red-50 text-red-600 border-red-200',
                                    'Lista para envío' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                    'Enviado' => 'bg-purple-50 text-purple-600 border-purple-200',
                                    'Disponible' => 'bg-emerald-600 text-white border-emerald-700',
                                    'Separado' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'Vendido' => 'bg-slate-900 text-white border-slate-900',
                                ];
                                $class = $statusClasses[$generator->status] ?? 'bg-slate-50 text-slate-500';
                            @endphp
                            <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase border {{ $class }} tracking-tighter shadow-sm">
                                {{ $generator->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-[10px] text-slate-500 font-medium">
                            {{ $generator->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('inventory.generators.show', $generator) }}" class="p-2 text-slate-400 hover:text-slate-900 bg-white border border-slate-100 rounded-lg shadow-sm" title="Ver Detalle">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <!-- Estas opciones solo serna visibles para el SuperAdmin -->
                                @if(Auth::user()->role === 'admin' )
                                <button onclick="openEditModal({{ $generator }})" class="p-2 text-slate-400 hover:text-blue-600 bg-white border border-slate-100 rounded-lg shadow-sm" title="Editar">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <form action="{{ route('inventory.generators.destroy', $generator) }}" method="POST" onsubmit="return confirm('¿De verdad estas seguro de eliminar?\n\n¡ADVERTENCIA!\nEsta acción también eliminará por completo todos los historiales, revisiones del taller, refacciones usadas, y cualquier registro ligado a este elemento.\n\n¡Esta acción es permanente y NO se puede deshacer!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 bg-white border border-slate-100 rounded-lg shadow-sm" title="Eliminar">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-box-open text-4xl text-slate-200 mb-4"></i>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No se encontraron generadores</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación Laravel -->
        <div class="p-6 bg-slate-50/50 border-t border-slate-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                    Mostrando {{ $generators->firstItem() ?? 0 }} a {{ $generators->lastItem() ?? 0 }} de {{ $generators->total() }} resultados
                </div>
                <div>
                    {{ $generators->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Generadores -->
<div id="modal-generator" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 id="modal-title" class="text-xl font-bold text-slate-900 uppercase tracking-tight">Nuevo Generador</h3>
                    <p id="modal-desc" class="text-slate-500 text-sm">Registra una nueva unidad en el inventario global.</p>
                </div>
                <button onclick="closeModal('modal-generator')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form id="generator-form" action="{{ route('inventory.generators.store') }}" method="POST">
                @csrf
                <div id="method-container"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
                            placeholder="Ej. SN-123456">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Folio Interno</label>
                        <input type="text" name="internal_folio" id="field-internal_folio" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Ej. FUO-001">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Costo (USD)</label>
                        <input type="number" step="0.01" name="cost" id="field-cost" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Estado</label>
                        <select name="status" id="field-status" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="Pedido en tránsito">Pedido en tránsito</option>
                            <option value="Recibido en almacén">Recibido en almacén</option>
                            <option value="En revisión">En revisión</option>
                            <option value="En taller">En taller</option>
                            <option value="Lista para envío">Lista para envío</option>
                            <option value="Enviado">Enviado</option>
                            <option value="Recibido en sucursal">Recibido en sucursal</option>
                            <option value="Disponible">Disponible</option>
                            <option value="Separado">Separado</option>
                            <option value="Vendido">Vendido</option>
                        </select>
                    </div>
                    @if(Auth::user()->role !== 'owner')
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Sucursal Actual</label>
                        <select name="current_branch_id" id="field-current_branch_id" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="">Ninguna / En tránsito</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modal-generator')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                    <button type="submit" class="flex-1 bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                        <i class="fas fa-save"></i> <span id="btn-text">Guardar Unidad</span>
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
                    <li><span class="font-bold text-slate-700">Costo USD</span> <span class="text-slate-400">(Opcional, numérico)</span></li>
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

<!-- Formulario Oculto para Eliminar por Lote -->
<form id="batch-delete-form" action="{{ route('inventory.generators.batch-destroy') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <input type="hidden" name="generator_ids" id="batch-delete-ids">
</form>

<!-- Modal para Actualización por Lote -->
<div id="modal-batch" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 uppercase tracking-tight">Actualización por Lote</h3>
                    <p class="text-slate-500 text-sm">Cambiar estado a <span id="modalBatchCount" class="font-bold text-slate-900 mx-1">0</span> unidades.</p>
                </div>
                <button onclick="closeModal('modal-batch')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form id="batch-form" action="{{ route('inventory.generators.batch-status') }}" method="POST">
                @csrf
                <input type="hidden" name="generator_ids" id="batch-ids">
                
                <div class="mb-4">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Nuevo Estado (Opcional)</label>
                    <select name="status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700 appearance-none">
                        <option value="">-- No cambiar estado --</option>
                        <option value="Pedido en tránsito">Pedido en tránsito</option>
                        <option value="Recibido en almacén">Recibido en almacén</option>
                        <option value="En revisión">En revisión</option>
                        <option value="En taller">En taller</option>
                        <option value="Lista para envío">Lista para envío</option>
                        <option value="Enviado">Enviado</option>
                        <option value="Recibido en sucursal">Recibido en sucursal</option>
                        <option value="Disponible">Disponible</option>
                        <option value="Separado">Separado</option>
                        <option value="Vendido">Vendido</option>
                    </select>
                </div>

                @if(Auth::user()->role !== 'owner')
                <div class="mb-4">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Asignar Sucursal / Cliente (Opcional)</label>
                    <select name="current_branch_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700 appearance-none">
                        <option value="">-- No cambiar sucursal --</option>
                        <option value="none">[Desasignar / En Tránsito]</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-6">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Comentario (Opcional)</label>
                    <input type="text" name="comment" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" placeholder="Ej. Actualización masiva de inventario">
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modal-batch')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                    <button type="submit" class="flex-1 bg-orange-600 text-white py-3 rounded-xl font-bold hover:bg-orange-700 shadow-lg shadow-orange-200 transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                        <i class="fas fa-check"></i> Aplicar a Todos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Logica Batch / Lote
    const selectAllCheckbox = document.getElementById('selectAll');
    const generatorCheckboxes = document.querySelectorAll('.generator-checkbox');
    const btnBatchUpdate = document.getElementById('btnBatchUpdate');
    const batchCountSpan = document.getElementById('batchCount');
    const btnBatchDelete = document.getElementById('btnBatchDelete');
    const batchDeleteCountSpan = document.getElementById('batchDeleteCount');
    const modalBatchCountSpan = document.getElementById('modalBatchCount');
    const batchIdsInput = document.getElementById('batch-ids');
    const batchDeleteIdsInput = document.getElementById('batch-delete-ids');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            generatorCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBatchButton();
        });
    }

    generatorCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBatchButton);
    });

    function updateBatchButton() {
        const selected = document.querySelectorAll('.generator-checkbox:checked');
        const count = selected.length;
        
        if (count > 0) {
            btnBatchUpdate.classList.remove('hidden');
            btnBatchUpdate.classList.add('flex');
            batchCountSpan.innerText = count;

            btnBatchDelete.classList.remove('hidden');
            btnBatchDelete.classList.add('flex');
            batchDeleteCountSpan.innerText = count;
        } else {
            btnBatchUpdate.classList.add('hidden');
            btnBatchUpdate.classList.remove('flex');

            btnBatchDelete.classList.add('hidden');
            btnBatchDelete.classList.remove('flex');
        }
        
        if(selectAllCheckbox) {
            selectAllCheckbox.checked = (count === generatorCheckboxes.length && count > 0);
        }
    }

    function submitBatchDelete() {
        const selected = document.querySelectorAll('.generator-checkbox:checked');
        if (selected.length === 0) return;

        if(!confirm(`¿De verdad estas seguro de eliminar ${selected.length} unidades seleccionadas?\n\n¡ADVERTENCIA!\nEsta acción también eliminará por completo todos los historiales, revisiones, pedidos y registros que estén ligados a estos elementos.\n\n¡Esta acción es permanente y NO se puede deshacer!`)) return;
        
        const ids = Array.from(selected).map(cb => cb.value);
        batchDeleteIdsInput.value = ids.join(',');
        document.getElementById('batch-delete-form').submit();
    }

    function openBatchModal() {
        const selected = document.querySelectorAll('.generator-checkbox:checked');
        if (selected.length === 0) return;
        
        const ids = Array.from(selected).map(cb => cb.value);
        batchIdsInput.value = ids.join(',');
        modalBatchCountSpan.innerText = selected.length;
        
        document.getElementById('modal-batch').classList.remove('hidden');
    }

    const modal = document.getElementById('modal-generator');
    const form = document.getElementById('generator-form');
    const title = document.getElementById('modal-title');
    const desc = document.getElementById('modal-desc');
    const btnText = document.getElementById('btn-text');
    const methodContainer = document.getElementById('method-container');
    
    // Inputs
    const inputModel = document.getElementById('field-model');
    const inputSerial = document.getElementById('field-serial_number');
    const inputFolio = document.getElementById('field-internal_folio');
    const inputCost = document.getElementById('field-cost');
    const inputStatus = document.getElementById('field-status');
    const inputBranch = document.getElementById('field-current_branch_id');

    function openCreateModal() {
        form.action = "{{ route('inventory.generators.store') }}";
        methodContainer.innerHTML = '';
        title.innerText = "Nuevo Generador";
        desc.innerText = "Registra una nueva unidad en el inventario global.";
        btnText.innerText = "Guardar Unidad";
        
        inputModel.value = '';
        inputSerial.value = '';
        inputFolio.value = '';
        inputCost.value = '';
        inputStatus.value = 'Pedido en tránsito';
        if (inputBranch) {
            inputBranch.value = '';
        }
        
        modal.classList.remove('hidden');
    }

    function openEditModal(generator) {
        form.action = `/inventory/generators/${generator.id}`;
        methodContainer.innerHTML = '@method("PUT")';
        title.innerText = "Editar Generador";
        desc.innerText = "Actualiza la información técnica de la unidad.";
        btnText.innerText = "Actualizar Cambios";
        
        inputModel.value = generator.model;
        inputSerial.value = generator.serial_number;
        inputFolio.value = generator.internal_folio;
        inputCost.value = generator.cost;
        inputStatus.value = generator.status;
        if (inputBranch) {
            inputBranch.value = generator.current_branch_id || '';
        }
        
        modal.classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection