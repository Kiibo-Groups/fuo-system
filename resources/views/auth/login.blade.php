@extends('layouts.guest')

@section('content')
 
<div class="text-center mb-8">
    <div
        class="inline-block bg-slate-900 p-4 rounded-2xl shadow-xl transform -rotate-3 hover:rotate-0 transition-all duration-300 mb-4">
        <i class="fas fa-charging-station text-orange-500 text-3xl"></i>
    </div>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">GEN-CONTROL</h1>
    <p class="text-slate-500 font-medium mt-1 uppercase text-xs tracking-widest">Gestión de Generadores v1.0</p>
</div>

 
<div id="login-view"
    class="bg-white rounded-3xl shadow-2xl shadow-slate-200 border border-slate-100 p-8 sm:p-10">
    <h2 class="text-2xl font-bold text-slate-800 mb-2 text-center">Bienvenido de nuevo</h2>
    <p class="text-slate-500 text-center mb-8 text-sm">Ingresa tus credenciales para acceder</p>

    <form action="{{ route('login') }}" method="POST" class="space-y-5">
        <!-- Directiva CSRF (Comentada para el preview) -->
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fas fa-envelope"></i>
                </span>
                <input type="email" name="email" value="" required
                    class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none bg-slate-50/50"
                    placeholder="usuario@empresa.com">
            </div>
            <!-- Directiva de Error Laravel -->
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex justify-between mb-1">
                <label class="text-sm font-semibold text-slate-700">Contraseña</label>
                <a href="#" class="text-xs font-bold text-orange-600 hover:text-orange-700">¿Olvidaste tu
                    contraseña?</a>
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" name="password" required
                    class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none bg-slate-50/50"
                    placeholder="••••••••">
            </div>
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center">
            <input type="checkbox" id="remember" name="remember"
                class="w-4 h-4 text-orange-600 border-slate-300 rounded focus:ring-orange-500">
            <label for="remember" class="ml-2 text-sm text-slate-600">Mantener sesión iniciada</label>
        </div>

        <button type="submit"
            class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl shadow-lg hover:bg-slate-800 transform hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
            Iniciar Sesión
            <i class="fas fa-arrow-right text-xs"></i>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
        <p class="text-sm text-slate-600">
            ¿No tienes acceso?
            <a href="{{ url('/register') }}" class="font-bold text-orange-600 hover:underline">Solicita una
                cuenta
            </a>
        </p>
    </div>
</div>

@endsection
