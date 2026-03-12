@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Gestión de Sucursales</h1>
            <p class="text-slate-500 font-medium">Administra las sedes físicas y puntos de venta.</p>
        </div>
        <button onclick="openCreateModal()" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
            <i class="fas fa-plus text-orange-500"></i> Nueva Sucursal
        </button>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Nombre / Sede</th>
                    <th class="px-6 py-4">Ubicación</th>
                    <th class="px-6 py-4 text-center">Comisión %</th>
                    <th class="px-6 py-4 text-center">Máquinas</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
                @foreach($branches as $branch)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-mono text-slate-400">#{{ str_pad($branch->id, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-900 uppercase">{{ $branch->name }}</div>
                    </td>
                    <td class="px-6 py-4 text-slate-600">{{ $branch->location }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block bg-orange-50 text-orange-600 border border-orange-100 text-xs font-black px-3 py-1 rounded-full">
                            {{ number_format($branch->commission_rate, 2) }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $branch->generators_count }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick='openEditModal({!! $branch->toJson() !!})' class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta sucursal?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Unificado -->
<div id="modal-branch" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 id="modal-title" class="text-xl font-bold text-slate-900 uppercase tracking-tight">Nueva Sucursal</h3>
                    <p id="modal-desc" class="text-slate-500 text-sm">Registra una nueva sede en el sistema.</p>
                </div>
                <button onclick="closeModal('modal-branch')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form id="branch-form" action="{{ route('admin.branches.store') }}" method="POST">
                @csrf
                <div id="method-container"></div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Nombre de la Sede</label>
                        <input type="text" name="name" id="field-name" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Ej. Monterrey Norte">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Ubicación / Dirección</label>
                        <input type="text" name="location" id="field-location" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Ej. Av. Solidaridad 450, Santa Catarina">
                    </div>
                    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
                        <label class="block text-xs font-black text-orange-700 uppercase tracking-widest mb-1">
                            <i class="fas fa-percentage mr-1"></i> Comisión de Sucursal (%)
                        </label>
                        <p class="text-[10px] text-orange-600 mb-2">Este % se suma al costo real del generador para obtener el precio que verá el owner.</p>
                        <input type="number" name="commission_rate" id="field-commission_rate"
                            min="0" max="100" step="0.01" value="0"
                            class="w-full px-4 py-3 bg-white border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-bold text-slate-700"
                            placeholder="Ej. 2.5">
                    </div>
                    
                    <div class="pt-4 flex gap-3">
                        <button type="button" onclick="closeModal('modal-branch')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                        <button type="submit" class="flex-1 bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                            <i class="fas fa-save"></i> <span id="btn-text">Guardar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-branch');
    const form = document.getElementById('branch-form');
    const title = document.getElementById('modal-title');
    const desc = document.getElementById('modal-desc');
    const btnText = document.getElementById('btn-text');
    const methodContainer = document.getElementById('method-container');
    
    // Inputs
    const inputName = document.getElementById('field-name');
    const inputLocation = document.getElementById('field-location');
    const inputCommission = document.getElementById('field-commission_rate');

    function openCreateModal() {
        form.action = "{{ route('admin.branches.store') }}";
        methodContainer.innerHTML = '';
        title.innerText = "Nueva Sucursal";
        desc.innerText = "Registra una nueva sede en el sistema.";
        btnText.innerText = "Guardar Sucursal";

        inputName.value = '';
        inputLocation.value = '';
        inputCommission.value = '0';

        modal.classList.remove('hidden');
    }

    function openEditModal(branch) {
        form.action = `/admin/branches/${branch.id}`;
        methodContainer.innerHTML = '@method("PUT")';
        title.innerText = "Editar Sucursal";
        desc.innerText = "Actualiza la información de la sede.";
        btnText.innerText = "Actualizar Cambios";

        inputName.value = branch.name;
        inputLocation.value = branch.location;
        inputCommission.value = branch.commission_rate ?? 0;

        modal.classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection
