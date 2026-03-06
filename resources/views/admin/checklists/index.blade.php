@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Configuración de Checklists</h1>
            <p class="text-slate-500 font-medium">Define los puntos de inspección obligatorios para los técnicos.</p>
        </div>
        <button onclick="openCreateModal()" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
            <i class="fas fa-plus text-orange-500"></i> Nueva Plantilla
        </button>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($templates as $template)
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-black text-slate-800 uppercase tracking-tight">{{ $template->title }}</h3>
                    <span class="text-[10px] font-bold {{ $template->is_active ? 'text-emerald-600' : 'text-slate-400' }} uppercase tracking-widest">
                        {{ $template->is_active ? '● Activo' : '○ Inactivo' }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <button onclick='openEditModal({!! $template->toJson() !!})' class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.checklists.destroy', $template) }}" method="POST" onsubmit="return confirm('¿Eliminar esta plantilla?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <ul class="space-y-2 mb-6">
                @foreach($template->items as $item)
                <li class="flex items-center gap-3 text-sm text-slate-600 bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                    <i class="fas fa-check-circle text-orange-500 text-[10px]"></i>
                    {{ $item }}
                </li>
                @endforeach
            </ul>

            <div class="pt-4 border-t border-slate-50 flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <span>Última modificación: {{ $template->updated_at->format('d/m/Y') }}</span>
                <span>{{ count($template->items) }} Puntos</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal para Crear/Editar Checklist -->
<div id="modal-checklist" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-200">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 id="modal-title" class="text-lg font-black text-slate-900 uppercase tracking-tight">Configurar Plantilla</h3>
            <button onclick="closeModal('modal-checklist')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="checklist-form" action="{{ route('admin.checklists.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div id="method-container"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-3">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">Nombre del Checklist</label>
                    <input type="text" name="title" id="field-title" required placeholder="Ej. Revisión de Entrada USA" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none text-sm font-semibold">
                </div>
                <div class="flex items-center gap-2 mb-3">
                    <input type="checkbox" name="is_active" id="field-active" value="1" checked class="w-4 h-4 text-orange-600 border-slate-300 rounded focus:ring-orange-500">
                    <label class="text-xs font-bold text-slate-600 uppercase">Activo</label>
                </div>
            </div>
            
            <div id="items-wrapper">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5 text-orange-600">Puntos de Revisión</label>
                <div id="items-container" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                    <!-- Aquí se agregan los puntos dinámicamente -->
                </div>
                <button type="button" onclick="addItem()" class="mt-3 w-full py-2 border-2 border-dashed border-slate-200 text-slate-400 rounded-xl text-xs font-bold uppercase hover:border-orange-200 hover:text-orange-500 transition-all">
                    <i class="fas fa-plus mr-1"></i> Agregar Punto
                </button>
            </div>

            <div class="pt-6 flex gap-3">
                <button type="button" onclick="closeModal('modal-checklist')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                <button type="submit" id="btn-submit" class="flex-1 py-3 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-lg transition-all uppercase tracking-widest">Guardar Checklist</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-checklist');
    const form = document.getElementById('checklist-form');
    const title = document.getElementById('modal-title');
    const btnSubmit = document.getElementById('btn-submit');
    const methodContainer = document.getElementById('method-container');
    const itemsContainer = document.getElementById('items-container');
    
    // Inputs
    const fieldTitle = document.getElementById('field-title');
    const fieldActive = document.getElementById('field-active');

    function addItem(value = '') {
        const div = document.createElement('div');
        div.className = 'flex gap-2 group';
        div.innerHTML = `
            <input type="text" name="items[]" value="${value}" required placeholder="Describa el punto de revisión..." class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-orange-500 outline-none">
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-300 hover:text-red-500 transition-colors p-2">
                <i class="fas fa-times"></i>
            </button>
        `;
        itemsContainer.appendChild(div);
    }

    function openCreateModal() {
        form.action = "{{ route('admin.checklists.store') }}";
        methodContainer.innerHTML = '';
        title.innerText = "Nueva Plantilla";
        btnSubmit.innerText = "Guardar Checklist";
        fieldTitle.value = '';
        fieldActive.checked = true;
        itemsContainer.innerHTML = '';
        addItem(); // Iniciar con uno vacío
        
        modal.classList.remove('hidden');
    }

    function openEditModal(template) {
        form.action = `/admin/checklists/${template.id}`;
        methodContainer.innerHTML = '@method("PUT")';
        title.innerText = "Editar Plantilla";
        btnSubmit.innerText = "Actualizar Checklist";
        fieldTitle.value = template.title;
        fieldActive.checked = template.is_active;
        
        itemsContainer.innerHTML = '';
        template.items.forEach(item => addItem(item));
        
        modal.classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection