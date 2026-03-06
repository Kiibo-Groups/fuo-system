<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta QR - {{ $generator->internal_folio }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
            .print-area { border: none !important; box-shadow: none !important; }
        }
        .qr-placeholder {
            background: radial-gradient(circle, #f8fafc 0%, #f1f5f9 100%);
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">

    <!-- Header Controles -->
    <div class="no-print bg-slate-900 text-white p-4 shadow-xl flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.generators.show', $generator) }}" class="text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h1 class="text-xs font-black uppercase tracking-widest">Vista previa de etiqueta</h1>
        </div>
        <button onclick="window.print()" class="bg-orange-500 hover:bg-orange-400 text-slate-900 px-6 py-2 rounded-full font-black text-xs uppercase tracking-tighter transition-all shadow-lg flex items-center gap-2">
            <i class="fas fa-print"></i> Imprimir Etiqueta
        </button>
    </div>

    <!-- Area de Impresión -->
    <div class="flex justify-center p-12">
        <div class="print-area bg-white w-[400px] border border-slate-200 shadow-2xl rounded-3xl overflow-hidden p-8 flex flex-col items-center">
            
            <!-- Logo / Branding UI -->
            <div class="flex items-center gap-2 mb-8">
                <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center">
                    <span class="text-orange-500 font-black text-sm italic">F</span>
                </div>
                <span class="text-slate-900 font-black uppercase tracking-tighter text-lg">FUO <span class="text-slate-400 font-bold tracking-normal italic text-sm">SYSTEM</span></span>
            </div>

            <!-- QR Code Simulation (Usando API pública de QR para visualizarlo de inmediato) -->
            <div class="qr-placeholder w-64 h-64 rounded-[2.5rem] border-8 border-slate-50 shadow-inner flex items-center justify-center mb-8 relative group">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $generator->internal_folio }}" alt="QR Code" class="w-48 h-48">
                <div class="absolute inset-0 border-2 border-orange-500/20 rounded-[2.5rem] pointer-events-none"></div>
            </div>

            <!-- Información del Equipo -->
            <div class="text-center w-full space-y-4">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-1">Folio de Control</p>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tighter uppercase">{{ $generator->internal_folio }}</h2>
                </div>

                <div class="flex justify-center gap-4 pt-4 border-t border-slate-50">
                    <div class="text-left">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Modelo</p>
                        <p class="text-xs font-black text-slate-800 uppercase">{{ $generator->model }}</p>
                    </div>
                    <div class="text-right border-l border-slate-100 pl-4">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">No. Serie</p>
                        <p class="text-xs font-bold text-slate-600 font-mono">{{ $generator->serial_number }}</p>
                    </div>
                </div>

                <div class="pt-6">
                    <p class="text-[9px] font-bold text-slate-400 italic">Escanee el código para ver el historial completo y estatus en tiempo real.</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Auto-print option commented out for development
        // window.onload = () => window.print();
    </script>
</body>
</html>
