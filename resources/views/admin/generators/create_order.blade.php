@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-8">
    <div class="mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-orange-600 font-bold text-xs uppercase tracking-widest hover:underline mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Volver al panel
        </a>
        <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Registro de Pedido EE.UU.</h1>
        <p class="text-slate-500">Registra el lote de generadores para seguimiento internacional.</p>
    </div>

    <form action="{{ route('admin.orders.usa.store') }}" method="POST" class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        @csrf
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Modelo -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Modelo de Generador</label>
                    <div class="relative">
                        <i class="fas fa-tag absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <input type="text" name="model" required placeholder="Ej. Predator 9000W Inverter"
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none transition-all font-bold text-slate-700">
                    </div>
                </div>

                <!-- Sucursal Destino -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Sucursal Destino Final</label>
                    <div class="relative">
                        <i class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <select name="branch_id" required class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none appearance-none font-bold text-slate-700">
                            <option value="">Seleccionar sede...</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cantidad -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Cantidad de Unidades</label>
                    <div class="relative">
                        <i class="fas fa-cubes absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <input type="number" name="quantity" required min="1" placeholder="Ej. 10"
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none transition-all font-bold text-slate-700">
                    </div>
                </div>

                <!-- Costo Unitario -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Costo Unitario de Adquisición (MXN)</label>
                    <div class="relative">
                        <i class="fas fa-dollar-sign absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <input type="number" step="0.01" name="cost" required placeholder="0.00"
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none transition-all font-bold text-slate-700">
                    </div>
                </div>
            </div>

            <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4 flex gap-4 items-start">
                <i class="fas fa-info-circle text-orange-500 mt-1"></i>
                <p class="text-xs text-orange-800 font-medium">
                    Al guardar este pedido, el sistema generará automáticamente los folios internos y establecerá el estado inicial como <span class="font-bold uppercase tracking-tighter">"Pedido en tránsito"</span>.
                </p>
            </div>
        </div>

        <div class="bg-slate-50 px-8 py-6 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Estado Inicial: Transito</span>
            <button type="submit" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl hover:bg-slate-800 transform hover:-translate-y-1 transition-all">
                Confirmar Registro de Pedido <i class="fas fa-plane-departure ml-2 text-orange-500"></i>
            </button>
        </div>
    </form>
</div>
@endsection
