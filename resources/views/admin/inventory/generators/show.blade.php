@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <!-- Encabezado con Estado -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.generators.index') }}" class="p-3 bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-slate-900 transition-all shadow-sm">
                <i class="fas fa-chevron-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase">{{ $generator->internal_folio }}</h1>
                    @php
                        $statusClasses = [
                            'Pedido en tránsito' => 'bg-blue-50 text-blue-600 border-blue-100',
                            'Recibido en almacén' => 'bg-slate-100 text-slate-600 border-slate-200',
                            'En revisión' => 'bg-orange-50 text-orange-600 border-orange-200',
                            'En taller' => 'bg-red-50 text-red-600 border-red-200',
                            'Lista para envío' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                            'Enviado' => 'bg-purple-50 text-purple-600 border-purple-200',
                            'Disponible' => 'bg-emerald-600 text-white border-emerald-700',
                            'Separado' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'Vendido' => 'bg-slate-900 text-white border-slate-900',
                        ];
                        $class = $statusClasses[$generator->status] ?? 'bg-slate-50 text-slate-500';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase border {{ $class }} shadow-sm tracking-widest">
                        {{ $generator->status }}
                    </span>
                </div>
                <p class="text-slate-500 font-medium flex items-center gap-2">
                    <i class="fas fa-barcode"></i> S/N: <span class="font-bold text-slate-700">{{ $generator->serial_number }}</span>
                </p>
            </div>
        </div>
        @if(Auth::user()->role === 'admin' )
        <div class="flex gap-3">
            <a href="{{ route('inventory.generators.qr', $generator) }}" target="_blank" class="bg-white border border-slate-200 text-slate-700 px-6 py-3 rounded-2xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-print"></i> Etiquetas QR
            </a>
            <button onclick="openStatusModal('{{ $generator->status }}')" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
                <i class="fas fa-exchange-alt text-orange-500"></i> Actualizar Estado
            </button>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Grid de Información -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Columna Principal -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Card de Datos Técnicos -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8">
                <!-- Imagen del Equipo (si existe) -->
                @if($generator->image)
                <div class="mb-6">
                    <img src="{{ Storage::url($generator->image) }}"
                        alt="{{ $generator->internal_folio }}"
                        class="w-full max-h-64 object-contain rounded-2xl border border-slate-100 bg-slate-50 shadow-sm"
                        onerror="this.classList.add('hidden')">
                </div>
                @endif
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                    <i class="fas fa-info-circle text-orange-500"></i> Especificaciones del Equipo
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Modelo</p>
                        <p class="text-lg font-bold text-slate-900">{{ $generator->model }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Sucursal Asignada</p>
                        <p class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            @if($generator->assignedBranch)
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                            {{ $generator->assignedBranch->name }}
                            @else
                            <span class="text-slate-400 text-sm font-medium">Sin asignar</span>
                            @endif
                        </p>
                        @if($generator->branch && $generator->branch->id !== optional($generator->assignedBranch)->id)
                        <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                            <i class="fas fa-map-pin"></i> Ubicación actual: <span class="font-bold">{{ $generator->branch->name }}</span>
                        </p>
                        @elseif(!$generator->branch)
                        <p class="text-[10px] text-slate-400 mt-1">Ubicación actual: En tránsito / Almacén</p>
                        @endif
                    </div>
                    <div>
                        @if(Auth::user()->role === 'owner')
                            {{-- Owner: solo ve el precio final asignado, sin desglose --}}
                            <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Precio Asignado</p>
                            @if($generator->owner_price)
                            <p class="text-lg font-bold text-slate-900">$ {{ number_format($generator->owner_price, 2) }} <span class="text-xs text-slate-400">MXN</span></p>
                            @else
                            <p class="text-sm text-slate-400 italic">Sin precio asignado</p>
                            @endif
                        @else
                            {{-- Admin: ve el costo real de adquisición --}}
                            <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Costo Adquisición</p>
                            <p class="text-lg font-bold text-orange-600">$ {{ number_format($generator->cost, 2) }} <span class="text-xs">MXN</span></p>
                            @if($generator->owner_price)
                            <div class="mt-1 space-y-0.5">
                                <p class="text-[10px] text-slate-400">Precio owner: ${{ number_format($generator->owner_price, 2) }}</p>
                                <p class="text-[10px] text-orange-500">Comisión ({{ optional($generator->assignedBranch)->commission_rate ?? 0 }}%): ${{ number_format($generator->commission_amount, 2) }}</p>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tabs de Historial -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="flex border-b border-slate-50">
                    <button id="tab-logistics-btn" onclick="switchTab('logistics')" class="px-8 py-5 text-sm font-black uppercase tracking-widest text-slate-900 border-b-2 border-orange-500 bg-slate-50/50">Movimientos Logísticos</button>
                    <button id="tab-maintenance-btn" onclick="switchTab('maintenance')" class="px-8 py-5 text-sm font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">Historial de Mantenimiento</button>
                </div>
                        
                <!-- Contenido Tab Logística -->
                <div id="tab-logistics" class="p-8 ">
                    @forelse($generator->statusHistory as $history)
                        <div class="flex gap-4 pb-6 border-l-2 border-slate-100 ml-3 pl-6 relative">
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-4 border-blue-500"></div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-black text-slate-400 line-through">{{ $history->previous_status }}</span>
                                    <i class="fas fa-arrow-right text-[10px] text-slate-300"></i>
                                    <span class="text-xs font-black text-slate-900 uppercase">{{ $history->new_status }}</span>
                                </div>
                                <p class="text-[10px] text-slate-500 font-medium mb-2">
                                    {{ $history->created_at->format('d/m/Y - H:i') }} • Por: {{ $history->user->name ?? 'Sistema' }}
                                </p>
                                @if($history->comment)
                                <div class="bg-blue-50/50 rounded-xl p-3 border border-blue-100/50">
                                    <p class="text-xs text-slate-600 italic">"{{ $history->comment }}"</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="fas fa-shipping-fast text-4xl text-slate-100 mb-4"></i>
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Sin movimientos logísticos registrados</p>
                        </div>
                    @endforelse
                </div>

                <!-- Contenido Tab Mantenimiento -->
                <div id="tab-maintenance" class="p-8 hidden">
                    @forelse($generator->revisions as $revision)
                        <div class="flex gap-4 pb-6 border-l-2 border-slate-100 ml-3 pl-6 relative">
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-4 border-emerald-500"></div>
                            <div>
                                <p class="text-xs font-black text-slate-900 uppercase">Revisión: {{ $revision->result }}</p>
                                <p class="text-[10px] text-slate-500 font-medium mb-2">
                                    {{ $revision->created_at->format('d/m/Y - H:i') }} • Por: {{ $revision->technician->name ?? 'Sistema' }}
                                </p>
                                <div class="bg-slate-50 rounded-xl p-3 inline-block">
                                    <p class="text-xs text-slate-600 italic">"{{ $revision->observations ?? 'Sin observaciones adicionales.' }}"</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="fas fa-history text-4xl text-slate-100 mb-4"></i>
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Sin registros de mantenimiento aún</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <!-- Columna Lateral -->
        <div class="space-y-8">
            <!-- Card de Disponibilidad -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 text-white shadow-xl shadow-slate-200">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-3 bg-white/10 rounded-2xl">
                        <i class="fas fa-calendar-check text-xl text-orange-500"></i>
                    </div>
                </div>
                <h4 class="text-xl font-bold mb-1">Status Comercial</h4>
                <p class="text-slate-400 text-xs font-medium mb-6 uppercase tracking-tight">
                    @if($generator->status == 'Disponible')
                        Equipo listo para entrega inmediata.
                    @elseif($generator->status == 'Vendido')
                        Equipo entregado al cliente final.
                    @else
                        {{ $generator->status }} - No disponible para venta.
                    @endif
                </p>
                <div class="pt-6 border-t border-white/10">
                    <div class="flex items-center justify-between text-xs font-black uppercase tracking-widest">
                        <span>Disponibilidad</span>
                        <span class="{{ $generator->status == 'Disponible' ? 'text-emerald-400' : 'text-orange-400' }}">
                            {{ $generator->status == 'Disponible' ? '🟢 Alta' : '🟡 En Proceso' }}
                        </span>
                    </div>
                </div>
            </div>

            @if(Auth::user()->role === 'admin' )
            <!-- Card de Quick Actions -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-4">Acciones Rápidas</h3>
                <div class="space-y-2">
                    <button onclick="openStatusModal('En revisión')" class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-orange-50 hover:text-orange-700 rounded-2xl transition-all group">
                        <span class="text-sm font-bold">Iniciar Revisión</span>
                        <i class="fas fa-clipboard-list text-slate-300 group-hover:text-orange-500"></i>
                    </button>
                    <button onclick="openStatusModal('Lista para envío')" class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 rounded-2xl transition-all group">
                        <span class="text-sm font-bold">Actualizar Estado</span>
                        <i class="fas fa-exchange-alt text-slate-300 group-hover:text-blue-500"></i>
                    </button>
                    @if($generator->status == 'Lista para envío')
                    <button onclick="openShipmentModal()" class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 rounded-2xl transition-all group">
                        <span class="text-sm font-bold">Marcar como Enviado</span>
                        <i class="fas fa-shipping-fast text-slate-300 group-hover:text-blue-500"></i>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Card: Reasignar Sucursal Destino -->
            <div class="bg-white rounded-3xl border border-emerald-100 shadow-sm p-6">
                <h3 class="text-xs font-black text-emerald-700 uppercase tracking-widest mb-1 flex items-center gap-2">
                    <i class="fas fa-building"></i> Sucursal Destino
                </h3>
                <p class="text-[10px] text-slate-400 mb-4">La sucursal asignada siempre verá este generador. Cambia esto solo si el destino final cambia.</p>
                <form action="{{ route('inventory.generators.update-status', $generator) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $generator->status }}">
                    <select name="assigned_branch_id"
                        class="w-full px-3 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-semibold text-slate-700 appearance-none text-sm mb-3">
                        <option value="none" {{ is_null($generator->assigned_branch_id) ? 'selected' : '' }}>Sin asignar (Inventario General)</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $generator->assigned_branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="comment" placeholder="Motivo del cambio (opcional)"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-emerald-400 mb-3">
                    <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black uppercase text-[10px] py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 tracking-widest">
                        <i class="fas fa-check"></i> Guardar Asignación
                    </button>
                </form>
            </div>
            @endif

            @if($generator->status == 'Separado' && $generator->currentReservation)
            <!-- Card de Separación -->
            <div class="bg-yellow-50 rounded-3xl border border-yellow-200 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-yellow-500/10 mb-4">
                    <i class="fas fa-handshake text-8xl"></i>
                </div>
                <h3 class="text-xs font-black text-yellow-800 uppercase tracking-widest mb-4 flex items-center gap-2 relative z-10">
                    <i class="fas fa-lock text-yellow-600"></i> Separación Activa
                </h3>
                
                <div class="space-y-3 relative z-10">
                    <div>
                        <p class="text-[10px] text-yellow-700/70 font-bold uppercase tracking-widest mb-1">Cliente/Vendedor</p>
                        <p class="text-sm font-black text-yellow-900">{{ $generator->currentReservation->client_name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-yellow-700/70 font-bold uppercase tracking-widest mb-1">Teléfono</p>
                        <p class="text-sm font-bold text-yellow-800">{{ $generator->currentReservation->client_phone }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-yellow-700/70 font-bold uppercase tracking-widest mb-1">Realizada el</p>
                        <p class="text-sm font-bold text-yellow-800">{{ $generator->currentReservation->created_at->format('d/m/Y - h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-yellow-700/70 font-bold uppercase tracking-widest mb-1">Expira el</p>
                        <p class="text-sm font-bold text-yellow-800">{{ $generator->currentReservation->expires_at->format('d/m/Y - h:i A') }}</p>
                    </div>
                </div>

                @if(Auth::user()->role === 'admin')
                <div class="mt-6 pt-4 border-t border-yellow-200/60 relative z-10">
                    <form action="{{ route('inventory.generators.release', $generator) }}" method="POST" onsubmit="return confirm('¿Está seguro de forzar la liberación de este equipo?');">
                        @csrf
                        <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-black uppercase text-[10px] py-3 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 tracking-widest">
                            <i class="fas fa-unlock"></i> Liberar Equipo
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @endif

            <!-- Card de Taller y Refacciones -->
            @if($generator->workshopLogs->count() > 0)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center justify-between">
                    <span><i class="fas fa-tools text-orange-500 mr-2"></i> Reporte de Taller</span>
                    <span class="bg-orange-50 text-orange-600 px-2 py-1 rounded-lg text-[10px]">{{ $generator->workshopLogs->count() }} Ingreso(s)</span>
                </h3>
                
                <div class="space-y-4">
                    @foreach($generator->workshopLogs->sortByDesc('completed_at') as $log)
                        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/50">
                            <!-- Cabecera del log -->
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $log->completed_at ? $log->completed_at->format('d/m/Y - H:i') : $log->created_at->format('d/m/Y - H:i') }}</p>
                                    <p class="text-xs font-bold text-slate-700 mt-1">Costo Total: <span class="text-orange-600">$ {{ number_format($log->total_repair_cost, 2) }} MXN</span></p>
                                </div>
                                
                                @if(Auth::user()->role === 'admin' )
                                <!-- Estado de Pago -->
                                <div class="text-right">
                                    @if(in_array(Auth::user()->role, ['admin', 'owner']))
                                    <form action="{{ route('operations.workshop.toggle_payment', $log) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest transition-all shadow-sm border {{ $log->is_paid ? 'bg-emerald-50 text-emerald-600 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100' }}">
                                            @if($log->is_paid) <i class="fas fa-check-circle mr-1"></i> Pagado @else <i class="fas fa-exclamation-circle mr-1"></i> Pendiente @endif
                                        </button>
                                    </form>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border tracking-widest inline-block {{ $log->is_paid ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200' }}">
                                            {{ $log->is_paid ? 'Pagado' : 'Pendiente' }}
                                        </span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            <!-- Refacciones -->
                            @if($log->sparePartsLog && $log->sparePartsLog->count() > 0)
                                <div class="mt-4 pt-4 border-t border-slate-200">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Refacciones Utilizadas:</p>
                                    <ul class="space-y-2">
                                        @foreach($log->sparePartsLog as $partLog)
                                            <li class="flex justify-between items-center text-xs">
                                                <span class="text-slate-600 font-medium">
                                                    <span class="font-bold text-slate-800">{{ $partLog->quantity }}x</span> {{ $partLog->sparePart->name }}
                                                </span>
                                                <span class="font-bold text-slate-700">$ {{ number_format($partLog->quantity * $partLog->cost_at_moment, 2) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif($log->diagnosis)
                                <div class="mt-3 pt-3 border-t border-slate-200">
                                    <p class="text-[10px] text-slate-500 italic">"{{ $log->diagnosis }}"</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Actualización de Estado -->
<div id="modal-status" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 uppercase tracking-tight">Actualizar Estado</h3>
                    <p class="text-slate-500 text-sm">Cambia el estatus operativo del equipo.</p>
                </div>
                <button onclick="closeModal('modal-status')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form action="{{ route('inventory.generators.update-status', $generator) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Nuevo Estado</label>
                        <select name="status" id="modal-status-select" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none font-semibold text-slate-700 appearance-none">
                            <option value="Recibido en almacén">Recibido en almacén</option>
                            <option value="En revisión">En revisión</option>
                            <option value="En taller">En taller</option>
                            <option value="Lista para envío">Lista para envío</option>
                            <option value="Enviado">Enviado</option>
                            <option value="Disponible">Disponible</option>
                            <option value="Separado">Separado</option>
                            <option value="Vendido">Vendido</option>
                        </select>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ubicación Física Actual <span class="normal-case font-normal">(opcional)</span></p>
                        <p class="text-[10px] text-slate-400 mb-2">Solo cambia dónde está físicamente. La sucursal asignada <span class="font-bold text-emerald-600">{{ $generator->assignedBranch->name ?? 'no asignada' }}</span> no cambia.</p>
                        <select name="current_branch_id"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none font-semibold text-slate-700 appearance-none text-sm">
                            <option value="">-- No cambiar ubicación --</option>
                            <option value="none" {{ is_null($generator->current_branch_id) ? 'selected' : '' }}>[Sin ubicación / En tránsito]</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $generator->current_branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Observaciones / Motivo</label>
                        <textarea name="comment" rows="3" placeholder="Escribe el motivo del cambio..."
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none font-semibold text-slate-700 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modal-status')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase">Cancelar</button>
                    <button type="submit" class="flex-1 bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                        <i class="fas fa-save text-orange-500"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Registrar Datos de Envío -->
<div id="modal-shipment" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Despachar Unidad</h3>
                <p class="text-[10px] font-bold text-orange-600 uppercase tracking-widest">GENERADOR: {{ $generator->internal_folio }}</p>
            </div>
            <button onclick="closeModal('modal-shipment')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('logistics.shipments.send') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="generator_id" value="{{ $generator->id }}">
            
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
    function switchTab(tab) {
        const maintBtn = document.getElementById('tab-maintenance-btn');
        const logBtn = document.getElementById('tab-logistics-btn');
        const maintTab = document.getElementById('tab-maintenance');
        const logTab = document.getElementById('tab-logistics');

        if (tab === 'maintenance') {
            maintBtn.classList.add('border-orange-500', 'text-slate-900', 'bg-slate-50/50');
            maintBtn.classList.remove('text-slate-400');
            logBtn.classList.remove('border-orange-500', 'text-slate-900', 'bg-slate-50/50');
            logBtn.classList.add('text-slate-400');
            
            maintTab.classList.remove('hidden');
            logTab.classList.add('hidden');
        } else {
            logBtn.classList.add('border-orange-500', 'text-slate-900', 'bg-slate-50/50');
            logBtn.classList.remove('text-slate-400');
            maintBtn.classList.remove('border-orange-500', 'text-slate-900', 'bg-slate-50/50');
            maintBtn.classList.add('text-slate-400');
            
            logTab.classList.remove('hidden');
            maintTab.classList.add('hidden');
        }
    }

    function openStatusModal(defaultStatus = null) {
        if (defaultStatus) {
            document.getElementById('modal-status-select').value = defaultStatus;
        }
        document.getElementById('modal-status').classList.remove('hidden');
    }

    function openShipmentModal() {
        document.getElementById('modal-shipment').classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection
