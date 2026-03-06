<header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 flex-shrink-0">
    <div class="flex items-center gap-4">
        <button class="lg:hidden text-slate-600">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <h2 class="text-lg font-bold text-slate-800">Panel de Control Global</h2>
    </div>
    <div class="flex items-center gap-4">
        <button class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors">
            <i class="fas fa-bell"></i>
            <span class="absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full border-2 border-white"></span>
        </button>
        <div class="h-8 w-px bg-slate-200 mx-2"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="text-sm font-bold text-slate-700 flex items-center gap-2 bg-slate-50 hover:bg-slate-100 hover:text-red-500 px-3 py-1.5 rounded-lg border border-slate-200 transition-colors">
                <i class="fas fa-sign-out-alt text-slate-400"></i>
                Cerrar Sesión
            </button>
        </form>
    </div>
</header>