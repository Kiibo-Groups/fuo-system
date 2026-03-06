<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Generadores - Autenticación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <!-- Contenedor Principal -->
    <div id="auth-container" class="w-full max-w-md">
        @yield('content')

        <!-- Footer -->
        <p class="mt-8 text-center text-[10px] text-slate-400 uppercase tracking-[0.2em] font-bold">
            Potencia · Control · Eficiencia
        </p>
    </div>
</body>
</html>