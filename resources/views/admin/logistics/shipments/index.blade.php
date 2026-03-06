@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <!-- Encabezado de Sección -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Control de Envíos y Logística</h1>
            <p class="text-slate-500 font-medium">Gestión de traslados a sucursales y seguimiento de guías.</p>
        </div>
        <div class="flex gap-3">
            <div class="bg-white px-4 py-2 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pendientes de Envío</span>
                <span class="bg-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg">12</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Columna Izquierda: Unidades Listas para Salida -->
        <div class="xl:col-span-1 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-slate-50/30">
                    <h3 class="font-black text-slate-800 uppercase tracking-tight text-sm">
                        <i class="fas fa-box-open mr-2 text-orange-500"></i> Listos para Salida
                    </h3>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($readyToShip as $unit)
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:border-orange-200 transition-all group">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $unit->internal_folio }}</span>
                            <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-tighter">Lista para envío</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase mb-1">{{ $unit->model }}</h4>
                        <div class="flex items-center gap-2 text-[10px] text-slate-500 mb-4">
                            <i class="fas fa-map-marker-alt"></i> Destino: <span class="font-bold text-slate-700 uppercase">{{ $unit->branch->name }}</span>
                        </div>
                        <button onclick="prepareShipment({{ $unit->id }}, '{{ $unit->internal_folio }}')" class="w-full py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all shadow-sm">
                            Gestionar Envío <i class="fas fa-truck-loading ml-1 text-orange-500"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">No hay unidades pendientes</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Envíos Activos (En Tránsito) -->
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 uppercase tracking-tight text-sm">
                        <i class="fas fa-truck-moving mr-2 text-blue-500"></i> Unidades en Tránsito
                    </h3>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                        <input type="text" placeholder="Buscar guía o folio..." class="pl-8 pr-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[10px] focus:outline-none focus:ring-2 focus:ring-orange-500 w-48 font-bold">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-bold tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Información de Envío</th>
                                <th class="px-6 py-4">Paquetería / Guía</th>
                                <th class="px-6 py-4">Evidencia</th>
                                <th class="px-6 py-4">Tiempo en Ruta</th>
                                <th class="px-6 py-4 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm">
                            @foreach($activeShipments as $shipment)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-black text-slate-900 tracking-tighter">{{ $shipment->generator->internal_folio }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase font-bold">{{ $shipment->generator->model }}</div>
                                    <div class="mt-1 flex items-center gap-1.5 text-[9px] font-black text-orange-600 uppercase">
                                        <i class="fas fa-arrow-right"></i> {{ $shipment->generator->branch->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-700 text-xs">{{ $shipment->shipping_company }}</div>
                                    <div class="font-mono text-[10px] text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded inline-block mt-1">
                                        {{ $shipment->tracking_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <button class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:bg-orange-50 hover:text-orange-500 transition-all flex items-center justify-center border border-slate-200">
                                        <i class="fas fa-image"></i>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $shipment->created_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-slate-300 hover:text-slate-900"><i class="fas fa-ellipsis-v"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Registrar Datos de Envío -->
<div id="modal-shipment" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Despachar Unidad</h3>
                <p id="modal-folio" class="text-[10px] font-bold text-orange-600 uppercase tracking-widest"></p>
            </div>
            <button onclick="closeModal('modal-shipment')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('logistics.shipments.send') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="generator_id" id="input-generator-id">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Paquetería</label>
                    <select name="shipping_company" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none appearance-none">
                        <option value="Castores">Castores</option>
                        <option value="Tres Guerras">Tres Guerras</option>
                        <option value="FedEx">FedEx</option>
                        <option value="Privado">Transporte Privado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Número de Guía</label>
                    <input type="text" name="tracking_number" required placeholder="N/A o Folio" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Evidencia Fotográfica</label>
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-orange-300 transition-colors cursor-pointer relative">
                    <input type="file" name="photo_evidence" required class="absolute inset-0 opacity-0 cursor-pointer">
                    <i class="fas fa-camera text-slate-300 text-2xl mb-2"></i>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Click para subir foto del equipo empacado</p>
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('modal-shipment')" class="flex-1 py-3 text-xs font-black text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                <button type="submit" class="flex-1 py-3 text-xs font-black text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-lg transition-all uppercase tracking-widest">Confirmar Envío</button>
            </div>
        </form>
    </div>
</div>

<script>
    function prepareShipment(id, folio) {
        document.getElementById('input-generator-id').value = id;
        document.getElementById('modal-folio').innerText = 'GENERADOR: ' + folio;
        document.getElementById('modal-shipment').classList.remove('hidden');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection