<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta #{{ $sale->id }}</title>
    <!-- Incluir Tailwind solo para el CSS básico de la vista, idealmente se debe tener un diseño puro e imprimible, pero Tailwind nos ayuda. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { 
                margin: 0; 
                padding: 0; 
                width: 80mm; 
                font-family: monospace; 
                color: #000;
            }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            * { text-shadow: none !important; box-shadow: none !important; background: transparent !important; }
            img { filter: grayscale(100%); }
            
        }
        @page {
            margin: 0;
            size: 80mm auto;
        }
    </style>
</head>
<body class="bg-gray-100 flex justify-center py-4 font-mono text-xs text-black" onload="window.print()">

    <!-- Tarjeta del Ticket (simulando 80mm) -->
    <div class="bg-white p-4 w-[80mm] shadow-lg print:shadow-none print:w-full max-w-full">
        <!-- Encabezado del Ticket -->
        <div class="text-center mb-4 border-b-2 border-dashed border-gray-400 pb-4">
            <h1 class="text-lg font-bold uppercase tracking-widest mb-1">GEN-CONTROL</h1>
            <p class="font-bold">Sucursal: {{ $sale->branch->name ?? 'Global' }}</p>
            <p>Venta: #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p>Fecha: {{ $sale->created_at->format('d/m/Y H:i') }}</p>
            <p>Atendió: {{ $sale->user->name ?? 'Empleado' }}</p>
        </div>

        <div class="mb-4">
            <p><strong>Cliente:</strong> {{ $sale->client_name ?: 'Mostrador' }}</p>
            <p><strong>Pago:</strong> {{ $sale->payment_method }}</p>
        </div>

        <!-- Detalles de Compra -->
        <div class="mb-4 text-[10px] w-full border-t border-b border-gray-400 py-2 border-dashed">
            <table class="w-full text-left">
                <thead>
                    <tr class="font-bold border-b border-gray-300">
                        <th class="py-1">Cant.</th>
                        <th class="py-1">Cod/Articulo</th>
                        <th class="py-1 text-right">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr class="align-top">
                            <td class="py-1">{{ $item->quantity }}</td>
                            <td class="py-1 break-words">
                                @if($item->sellable_type === 'App\Models\Generator')
                                    [GEN] {{ $item->sellable->model ?? 'Generador' }}<br>
                                    SN: {{ $item->sellable->serial_number ?? 'N/A' }}
                                @else
                                    {{ $item->sellable->name ?? 'Articulo' }}
                                @endif
                            </td>
                            <td class="py-1 text-right">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="flex flex-col items-end border-b-2 border-dashed border-gray-400 pb-4 mb-4">
            <div class="flex justify-between w-full font-bold text-sm">
                <span>TOTAL MXN:</span>
                <span>${{ number_format($sale->total_amount, 2) }}</span>
            </div>
            <p class="text-[9px] text-gray-500 mt-1">Garantía válida bajo términos.</p>
        </div>

        <!-- Pie de Ticket -->
        <div class="text-center text-[10px]">
            <p class="font-bold mb-1">¡Gracias por tu compra!</p>
            <p>Escanea este código o revisa las políticas<br>de garantía con nosotros.</p>
            <div class="mt-4 no-print">
                <button onclick="window.print()" class="px-3 py-1 bg-gray-900 border border-gray-900 text-white rounded font-bold hover:bg-gray-800 focus:outline-none">Imprimir</button>
            </div>
        </div>
    </div>
</body>
</html>
