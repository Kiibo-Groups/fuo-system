@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Productos Disponibles</h1>
            <p class="text-slate-500 font-medium">Asigna precios de venta a tu inventario disponible.</p>
        </div>
        <div>
            <a href="{{ route('owner.pos.index') }}" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-sm hover:bg-indigo-700 transition-all text-sm flex items-center gap-2">
                <i class="fas fa-cash-register"></i> Ir al POS
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3">
        <i class="fas fa-check-circle text-xl"></i>
        <p class="font-bold text-sm">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden p-6 mb-8">
        <form action="{{ route('owner.pos.products') }}" method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por modelo, serie o folio..." class="flex-1 bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:ring-2 focus:ring-orange-500 px-4 py-3 text-sm">
            <button type="submit" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-medium hover:bg-slate-800 transition-colors">
                Buscar
            </button>
        </form>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex-1 flex flex-col min-h-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Folio / Serie</th>
                        <th class="px-6 py-4">Modelo del Generador</th>
                        <th class="px-6 py-4 text-center">Stock</th>
                        <th class="px-6 py-4 text-right">Costo MXN</th>
                        <th class="px-6 py-4 text-right">Imagen y Precio de Venta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($generators as $generator)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-900 tracking-tighter">{{ $generator->internal_folio }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $generator->serial_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-700">{{ $generator->model }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-emerald-100 text-emerald-700 px-2 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider">
                                1
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-black text-slate-900">
                           
                            @if($generator->owner_price)
                            <span class="font-black text-slate-900 text-sm">${{ number_format($generator->owner_price, 2) }}</span>
                            @else
                            <span class="text-[10px] text-slate-400 italic">Sin precio</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('owner.pos.update_price', $generator) }}" method="POST" enctype="multipart/form-data" class="flex items-center justify-end gap-4">
                                @csrf
                                @method('PUT')
                                
                                <div class="flex items-center gap-3">
                                    @if($generator->image)
                                        <img src="{{ Storage::url($generator->image) }}" alt="Img" class="w-10 h-10 object-cover rounded-xl border border-slate-200 shadow-sm">
                                    @else
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                    <label class="cursor-pointer bg-white hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-xl text-xs font-bold transition-colors border border-slate-200 shadow-sm">
                                        <i class="fas fa-upload mr-1"></i> Subir
                                        <input type="file" name="image" class="hidden" accept="image/*" onchange="this.form.querySelector('.upload-indicator').classList.remove('hidden')">
                                    </label>
                                    <span class="upload-indicator hidden text-orange-500 font-bold text-xs"><i class="fas fa-asterisk"></i> Archivo listo</span>
                                </div>

                                <div class="relative w-32 border-l border-slate-100 pl-4">
                                    <span class="absolute left-7 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $generator->sale_price) }}" required 
                                        class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-900 outline-none text-right shadow-sm">
                                </div>
                                <button type="submit" class="bg-orange-500 text-white p-2.5 rounded-xl font-bold hover:bg-orange-600 transition-colors shadow-sm" title="Guardar Cambios">
                                    <i class="fas fa-save text-sm"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">
                            No hay equipos disponibles en tu sucursal
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $generators->links() }}
    </div>
</div>
@endsection
