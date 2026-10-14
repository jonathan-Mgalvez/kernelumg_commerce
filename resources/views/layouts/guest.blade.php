<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body>
        <div class="min-h-screen grid lg:grid-cols-2">
            <div class="hidden lg:flex flex-col justify-between p-10 bg-primary-container text-white relative overflow-hidden">
                <img src="{{ asset('img/productos/varilla-corrugada.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-20">
                <a href="{{ route('home') }}" class="relative flex items-center gap-2">
                    <span class="material-symbols-outlined text-[28px]">precision_manufacturing</span>
                    <span class="text-headline-md uppercase tracking-wider">FerroTek</span>
                </a>
                <div class="relative">
                    <p class="text-label-sm uppercase text-on-primary-container">Portal de clientes</p>
                    <p class="mt-2 text-display-lg max-w-md">Cotiza materiales industriales con stock en tiempo real.</p>
                    <p class="mt-3 text-body-lg text-primary-fixed max-w-md">{{ config('empresa.eslogan') }}</p>
                </div>
                <p class="relative text-label-md text-on-primary-container">© {{ date('Y') }} FerroTek</p>
            </div>

            <div class="flex flex-col justify-center items-center px-4 py-10">
                <a href="{{ route('home') }}" class="lg:hidden mb-6 flex items-center gap-2 text-primary">
                    <span class="material-symbols-outlined text-[28px]">precision_manufacturing</span>
                    <span class="text-headline-md uppercase tracking-wider">FerroTek</span>
                </a>
                <div class="w-full max-w-md card p-6 sm:p-8">
                    {{ $slot }}
                </div>
                <a href="{{ route('home') }}" class="mt-4 text-label-md text-muted hover:underline">← Volver al sitio</a>
            </div>
        </div>
    </body>
</html>
