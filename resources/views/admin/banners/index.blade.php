@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Banners Publicitarios</h1>
            <p class="text-slate-500 font-medium">Gestión de publicidad y comunicados.</p>
        </div>
        <div>
            <a href="{{ route('admin.banners.create') }}" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-sm hover:bg-orange-700 transition-all text-sm flex items-center gap-2">
                <i class="fas fa-plus"></i> Nuevo Banner
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3">
        <i class="fas fa-check-circle text-xl"></i>
        <p class="font-bold text-sm">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Orden</th>
                        <th class="px-6 py-4">Banner</th>
                        <th class="px-6 py-4">Audiencia</th>
                        <th class="px-6 py-4">URL Destino</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($banners as $banner)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-black text-slate-400">#{{ $banner->order }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-32 h-16 bg-slate-100 rounded-lg overflow-hidden border border-slate-200">
                                    <img src="{{ Storage::url($banner->image_path) }}" class="w-full h-full object-cover" alt="Banner">
                                </div>
                                <div class="font-bold text-slate-800 text-xs">{{ $banner->title ?? 'Sin título' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($banner->target_audience == 'both')
                                <span class="bg-blue-50 text-blue-600 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Todos</span>
                            @elseif($banner->target_audience == 'owner')
                                <span class="bg-purple-50 text-purple-600 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Owners</span>
                            @else
                                <span class="bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Clientes</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($banner->target_url)
                                <a href="{{ $banner->target_url }}" target="_blank" class="text-blue-500 hover:text-blue-700 text-xs font-bold underline"><i class="fas fa-external-link-alt"></i> Ver Enlace</a>
                            @else
                                <span class="text-slate-300 text-xs font-bold">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($banner->is_active)
                                <span class="text-emerald-500 text-xs font-bold flex items-center gap-1"><i class="fas fa-check-circle"></i> Activo</span>
                            @else
                                <span class="text-slate-400 text-xs font-bold flex items-center gap-1"><i class="fas fa-times-circle"></i> Inactivo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="p-2 text-slate-400 hover:text-blue-600 bg-white border border-slate-100 rounded-lg shadow-sm transition-all" title="Editar">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('¿Seguro de eliminar este banner?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 bg-white border border-slate-100 rounded-lg shadow-sm transition-all" title="Eliminar">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">
                            No hay banners registrados en el sistema
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
