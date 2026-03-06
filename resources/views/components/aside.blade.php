<aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 hidden lg:flex flex-col border-r border-slate-800">
    <div class="p-6 flex items-center gap-3">
        <div class="bg-orange-500 p-2 rounded-lg text-slate-900">
            <i class="fas fa-charging-station text-xl"></i>
        </div>
        <span class="text-white font-bold text-xl tracking-tight">GEN-CONTROL</span>
    </div>

    <nav class="flex-1 px-4 space-y-1">
        
        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'owner')
            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold px-3 mb-2">Principal</p>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 @if(request()->routeIs('admin.dashboard')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-th-large w-5"></i> Dashboard
            </a>
            <a href="{{ route('inventory.generators.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('inventory.generators.index')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-boxes w-5"></i> Inventario Global
            </a>
        @endif

        @if(Auth::user()->role === 'admin')
        <a href="{{ route('admin.orders.usa') }}"
            class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('admin.orders.usa')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
            <i class="fas fa-ship w-5"></i> Pedidos EE.UU.
        </a>
        @endif

        
        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'owner')
            <a href="{{ route('logistics.shipments.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('logistics.shipments.index')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-truck w-5"></i> Envíos Activos
            </a>
        @endif

        @if(Auth::user()->role === 'owner')
            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold px-3 mt-6 mb-2">Ventas</p>
            <a href="{{ route('owner.pos.products') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('owner.pos.products')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-tags w-5"></i> Disp. para Venta
            </a>
            <a href="{{ route('owner.pos.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('owner.pos.index')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-cash-register w-5"></i> Punto de Venta
            </a>
        @endif

        @if(Auth::user()->role === 'admin' )
            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold px-3 mt-6 mb-2">Administración</p>
        @endif

        @if(Auth::user()->role === 'admin')
            <a href="{{ route('admin.branches.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('admin.branches.index')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-store w-5"></i> Sucursales
            </a>
        @endif
        @if(Auth::user()->role === 'admin')
            <a href="{{ route('admin.users.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('admin.users.index')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-users w-5"></i> Usuarios
            </a>
            <a href="{{ route('admin.checklists.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('admin.checklists.index')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-clipboard-check w-5"></i> Checklists
            </a>
            <a href="{{ route('admin.banners.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('admin.banners.*')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-images w-5"></i> Banners
            </a>
        @endif
        
        @if(Auth::user()->role !== 'client' && Auth::user()->role !== 'owner')
            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold px-3 mt-6 mb-2">Taller y Logística</p>
        @endif
        
        @if(Auth::user()->role === 'admin' )
            <a href="{{ route('inventory.spare-parts.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('inventory.spare-parts.index')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-tools w-5"></i> Control Refacciones
            </a>
        @endif
        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'technician')
            <a href="{{ route('operations.revisions.scan') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('operations.revisions.scan')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-qrcode w-5"></i> Escáner de Revisión
            </a>
            <a href="{{ route('operations.workshop.index') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('operations.workshop.*')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-wrench w-5"></i> Taller de Reparación
            </a>
        @endif

        

        @if(Auth::user()->role === 'client')
            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold px-3 mt-6 mb-2">Comprar Equipo</p>
            <a href="{{ route('store.available') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('store.available')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-store w-5"></i> Catálogo de Sucursal
            </a>
            <a href="{{ route('store.reservations') }}"
                class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800 @if(request()->routeIs('store.reservations')) bg-slate-800 text-white @else hover:bg-slate-800 hover:text-white @endif rounded-xl transition-all text-sm">
                <i class="fas fa-bookmark w-5"></i> Mis Separaciones
            </a>
        @endif
    </nav>
    <div class="p-4 border-t border-slate-800">
        <div class="bg-slate-800/50 p-4 rounded-2xl flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-slate-900 font-bold">
                {{ Auth::user()->name[0] }}
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>