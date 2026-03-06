@extends('layouts.guest')

@section('content') 
<div id="register-view"
    class="bg-white rounded-3xl shadow-2xl shadow-slate-200 border border-slate-100 p-8 sm:p-10">
    <h2 class="text-2xl font-bold text-slate-800 mb-2 text-center">Registro de Sucursal</h2>
    <p class="text-slate-500 text-center mb-8 text-sm">Crea una cuenta para tu nueva sede</p>

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre Completo</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" name="name" required
                    class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none bg-slate-50/50"
                    placeholder="Nombre del responsable">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Sucursal Asignada</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fas fa-building"></i>
                </span>
                <select name="branch_id"
                    class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none bg-slate-50/50 appearance-none">
                    <option value="">Selecciona sucursal...</option>
                    <option value="1">Monterrey (Matriz)</option>
                    <option value="2">CDMX</option>
                    <option value="3">Guadalajara</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fas fa-envelope"></i>
                </span>
                <input type="email" name="email" required
                    class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none bg-slate-50/50"
                    placeholder="correo@empresa.com">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Contraseña</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" name="password" required
                    class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none bg-slate-50/50"
                    placeholder="Mínimo 8 caracteres">
            </div>
        </div>

        <button type="submit"
            class="w-full bg-orange-600 text-white font-bold py-3.5 rounded-xl shadow-lg hover:bg-orange-700 transform hover:-translate-y-0.5 transition-all mt-4">
            Registrar Cuenta de Usuario
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
        <p class="text-sm text-slate-600">
            ¿Ya tienes una cuenta?
            <a href="{{ url('/login') }}" class="font-bold text-slate-900 hover:underline">Inicia
                sesión
            </a>
        </p>
    </div>
</div>
@endsection