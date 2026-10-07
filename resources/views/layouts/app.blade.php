<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KernelUMG Commerce')</title>

    <!-- Hojas de Estilo Empaquetadas con Vite -->
    @vite(['resources/css/app.css', 'resources/css/responsive.css', 'resources/css/bot.css'])
    @yield('styles')
</head>
<body>
    @if(view()->exists('partials.navbar'))
        @include('partials.navbar')
    @endif

    <main class="container">
        @if(view()->exists('partials.alerts'))
            @include('partials.alerts')
        @endif

        @yield('content')
    </main>

    @if(view()->exists('partials.footer'))
        @include('partials.footer')
    @endif

    @if(view()->exists('partials.chatbot'))
        @include('partials.chatbot')
    @endif

    <!-- Bundles JavaScript Optimizados con Vite -->
    @vite(['resources/js/app.js', 'resources/js/chatbot.js'])
    @yield('scripts')
</body>
</html>