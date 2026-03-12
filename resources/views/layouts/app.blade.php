<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - GEN-CONTROL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    <!-- Barra Lateral (Sidebar) -->
    @include('components.aside')

    <!-- Contenido Principal -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header Superior -->
        @include('components.header')

        <!-- Área de Scroll -->
        <div class="flex-1 overflow-y-auto">
            @yield('content')
        </div>
    </main>

    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>