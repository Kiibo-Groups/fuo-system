@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Control de Usuarios</h1>
            <p class="text-slate-500 font-medium">Administra el personal, roles y accesos por sucursal.</p>
        </div>
        <button onclick="openCreateModal()" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
            <i class="fas fa-user-plus text-orange-500"></i> Nuevo Usuario
        </button>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl text-sm font-bold">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-2">
                <select class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 outline-none focus:ring-2 focus:ring-orange-500">
                    <option>Todos los Roles</option>
                    <option>Admin General</option>
                    <option>Dueño Sucursal</option>
                    <option>Técnico</option>
                </select>
            </div>
            <div class="relative w-full md:w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" placeholder="Buscar usuario..." class="w-full pl-8 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-orange-500 outline-none">
            </div>
        </div>
        
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest">
                <tr>
                    <th class="px-6 py-4">Usuario</th>
                    <th class="px-6 py-4">Rol</th>
                    <th class="px-6 py-4">Sucursal</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-900 font-bold text-xs uppercase text-center leading-10">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border
                            {{ $user->role == 'admin' ? 'bg-slate-900 text-white border-slate-800' : 'bg-blue-50 text-blue-700 border-blue-100' }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 font-medium">{{ $user->branch->name ?? 'Global' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2 text-slate-400">
                            <button onclick='openEditModal({!! $user->toJson() !!})' class="hover:text-blue-600 transition-colors"><i class="fas fa-edit"></i></button>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hover:text-red-600 transition-colors"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Unificado -->
<div id="modal-user" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 id="modal-title" class="text-xl font-bold text-slate-900 uppercase tracking-tight">Nuevo Usuario</h3>
                    <p id="modal-desc" class="text-slate-500 text-sm">Crea un nuevo acceso para el sistema.</p>
                </div>
                <button onclick="closeModal('modal-user')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form id="user-form" action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div id="method-container"></div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Nombre Completo</label>
                        <input type="text" name="name" id="field-name" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Ej. Juan Pérez">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Correo Electrónico</label>
                        <input type="email" name="email" id="field-email" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="usuario@empresa.com">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Rol</label>
                            <select name="role" id="field-role" required 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700 appearance-none">
                                <option value="client">Cliente</option>
                                <option value="technician">Técnico</option>
                                <option value="owner">Dueño Sucursal</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Sucursal</label>
                            <select name="branch_id" id="field-branch"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700 appearance-none">
                                <option value="">Global / Sede</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Contraseña</label>
                        <input type="password" name="password" id="field-password" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none font-semibold text-slate-700" 
                            placeholder="Mínimo 8 caracteres">
                        <p id="password-hint" class="text-[10px] text-slate-400 mt-1 hidden italic">* Deja en blanco para mantener la actual</p>
                    </div>
                    
                    <div class="pt-4 flex gap-3">
                        <button type="button" onclick="closeModal('modal-user')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                        <button type="submit" class="flex-1 bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                            <i class="fas fa-save"></i> <span id="btn-text">Crear Usuario</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-user');
    const form = document.getElementById('user-form');
    const title = document.getElementById('modal-title');
    const desc = document.getElementById('modal-desc');
    const btnText = document.getElementById('btn-text');
    const methodContainer = document.getElementById('method-container');
    const passwordHint = document.getElementById('password-hint');
    
    // Inputs
    const inputName = document.getElementById('field-name');
    const inputEmail = document.getElementById('field-email');
    const inputRole = document.getElementById('field-role');
    const inputBranch = document.getElementById('field-branch');
    const inputPassword = document.getElementById('field-password');

    function openCreateModal() {
        form.action = "{{ route('admin.users.store') }}";
        methodContainer.innerHTML = '';
        title.innerText = "Nuevo Usuario";
        desc.innerText = "Crea un nuevo acceso para el sistema.";
        btnText.innerText = "Crear Usuario";
        passwordHint.classList.add('hidden');
        inputPassword.required = true;
        
        inputName.value = '';
        inputEmail.value = '';
        inputRole.value = 'client';
        inputBranch.value = '';
        inputPassword.value = '';
        
        modal.classList.remove('hidden');
    }

    function openEditModal(user) {
        form.action = `/admin/users/${user.id}`;
        methodContainer.innerHTML = '@method("PUT")';
        title.innerText = "Editar Usuario";
        desc.innerText = "Actualiza los datos y permisos del usuario.";
        btnText.innerText = "Actualizar Usuario";
        passwordHint.classList.remove('hidden');
        inputPassword.required = false;
        
        inputName.value = user.name;
        inputEmail.value = user.email;
        inputRole.value = user.role;
        inputBranch.value = user.branch_id || '';
        inputPassword.value = '';
        
        modal.classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection