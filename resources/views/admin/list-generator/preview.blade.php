@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-4 md:p-8 bg-slate-50">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden" id="printable-area">
        
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 d-print-none bg-slate-50/50">
            <div>
                <h5 class="text-xl font-black text-slate-800 uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-500"></i> Listado Generado
                </h5>
                <p class="text-xs text-slate-500 font-bold mt-1">
                    Parámetros: {{ $percentage * 100 }}% | Tipo de Cambio: ${{ number_format($exchangeRate, 2) }} | Multiplicador: {{ $multiplier }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.list-generator.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-sm shadow-sm hover:bg-emerald-700 transition-all flex items-center gap-2">
                    <i class="fas fa-print"></i> Imprimir a PDF
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead class="bg-slate-800 text-white text-[11px] uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">#</th>
                        <th class="px-4 py-3 w-32">SKU</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3 text-center w-32">Precio<br>Listado</th>
                        <th class="px-4 py-3 text-center w-32 text-emerald-300">Precio Venta<br><span class="text-[9px] font-normal opacity-80">(Redondeado)</span></th>
                        <th class="px-4 py-3 text-center w-32">Foto</th>
                        <th class="px-4 py-3 text-center w-32">Separación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach ($items as $index => $item)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-3 text-center text-slate-400 font-black">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-black text-slate-700">{{ $item['sku'] }}</td>
                        <td class="px-4 py-3 text-slate-600 font-medium">{{ $item['description'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-500 font-bold">${{ number_format($item['list_price'], 2) }}</td>
                        <td class="px-4 py-3 text-center text-emerald-600 font-black text-lg">
                            $ {{ number_format($item['sale_price'], 0) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($item['image_url'])
                                <div class="w-20 h-20 mx-auto bg-white rounded-lg border border-slate-200 p-1 flex items-center justify-center">
                                    <img src="{{ $item['image_url'] }}" alt="SKU {{ $item['sku'] }}" class="max-w-full max-h-full object-contain">
                                </div>
                            @else
                                <span class="text-slate-300 text-xs font-bold bg-slate-50 px-2 py-1 rounded">Sin Foto</span>
                            @endif
                        </td>
                        <td class="px-4 py-3"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        @page { size: landscape; margin: 1cm; }
        body { background: white; margin: 0; padding: 0; }
        body * { visibility: hidden; }
        #printable-area, #printable-area * { visibility: visible; }
        #printable-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; box-shadow: none; border: none; }
        
        .d-print-none { display: none !important; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px; }
        th { background-color: #f8fafc !important; color: #1e293b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bg-slate-50 { background-color: white !important; }
        .bg-slate-800 { background-color: #1e293b !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        img { max-width: 100px; max-height: 100px; }
    }
</style>
@endsection
