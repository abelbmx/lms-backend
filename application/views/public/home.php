<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LMS Administrativo</title>
    <meta name="description" content="Panel administrativo del LMS — cliente React + API REST">

    <!-- Tailwind via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Variables de entorno para React -->
    <script>
        window.__ENV__ = {
            API_BASE_URL: '<?= getenv('API_BASE_URL') ?: base_url('api') ?>'
        };
    </script>
</head>

<body class="bg-gray-100 font-sans">

    <!-- Navegación fija -->
    <nav class="bg-white shadow fixed w-full z-20">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="#" class="text-xl font-bold text-gray-800">LMS Admin</a>
            <ul class="hidden md:flex space-x-6 items-center">
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Inicio</a></li>
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Usuarios</a></li>
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Cursos</a></li>
            </ul>
            <div class="flex items-center space-x-4">
                <!-- Botón login/logout -->
                <?php if (isset($logout_link) && $logout_link): ?>
                    <a href="<?= base_url('auth/logout/public') ?>"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg">
                        Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('auth/login') ?>"
                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                        Iniciar Sesión
                    </a>
                <?php endif; ?>

                <!-- Botón movil -->
                <button class="md:hidden text-gray-800 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <main class="pt-20 flex-grow">
        <!-- Contenedor de carga -->
        <div id="loading" class="fixed inset-0 bg-white bg-opacity-75 flex flex-col items-center justify-center z-10">
            <svg class="animate-spin h-12 w-12 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span class="mt-4 text-gray-700">Cargando panel administrativo…</span>
        </div>

        <!-- Punto de montaje React con fallback estático -->
        <div id="root" class="container mx-auto px-4">
            <div class="text-center py-20">
                <h1 class="text-4xl font-bold text-gray-800">Bienvenido al Panel Administrativo LMS</h1>
                <p class="mt-2 text-gray-600">Si la aplicación no carga en unos segundos, verifica tu conexión.</p>
            </div>
        </div>
    </main>

    <!-- Bundle de React -->
    <script src="<?= base_url('static/js/main.bundle.js') ?>"></script>
    <script>
        // Al montar React quitamos el spinner
        document.getElementById('loading')?.remove();
    </script>
</body>

</html>
