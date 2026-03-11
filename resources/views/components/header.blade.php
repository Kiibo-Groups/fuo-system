<header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 flex-shrink-0 w-full">
    <div class="flex items-center gap-3 sm:gap-4 min-w-0 pr-2">
        <button onclick="openSidebar()" class="lg:hidden text-slate-600 hover:text-slate-900 p-1 flex-shrink-0">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <h2 class="text-base sm:text-lg font-bold text-slate-800 truncate">Panel de Control</h2>
    </div>
    <div class="flex items-center gap-3 sm:gap-4 flex-shrink-0">
        <!-- <button class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors">
            <i class="fas fa-bell"></i>
            <span class="absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full border-2 border-white"></span>
        </button> 
         <div class="hidden sm:block h-8 w-px bg-slate-200 mx-2"></div>-->     
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-slate-900 font-bold">
                {{ Auth::user()->name[0] }}
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>

        
    </div>
</header>

<script>
    function openSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('mobileBackdrop');
        
        if (sidebar && backdrop) {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            setTimeout(() => { backdrop.classList.remove('opacity-0'); backdrop.classList.add('opacity-100'); }, 10);
            document.body.style.overflow = 'hidden'; // prevent scrolling underneath
        }
    }

    function closeSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('mobileBackdrop');
        
        if (sidebar && backdrop) {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            setTimeout(() => { backdrop.classList.add('hidden'); }, 300);
            document.body.style.overflow = '';
        }
    }
</script>