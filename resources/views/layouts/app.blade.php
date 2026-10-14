<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        <!-- Encabezado de página -->
        @isset($header)
            <section class="bg-surface-container-low border-b border-line">
                <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6">
                    {{ $header }}
                </div>
            </section>
        @endisset

        @include('layouts.partials.flash')

        <main class="flex-1">
            {{ $slot }}
        </main>

        @include('layouts.partials.footer')
    </body>
</html>
