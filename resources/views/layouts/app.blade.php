<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KernelUMG Commerce')</title>
    <link rel="stylesheet" href="{{ asset('css/bot.css') }}">
    @yield('styles')
</head>
<body>
    <main>
        @yield('content')
    </main>

    @include('partials.chatbot')

    <script src="{{ asset('js/chatbot.js') }}"></script>
</body>
</html>