<x-app-layout>
    <x-slot:title>Nosotros</x-slot:title>
    <x-slot name="header">
        <p class="overline">Información institucional</p>
        <h1 class="text-display-lg text-primary">Sobre FerroTek</h1>
        <p class="mt-1 text-body-lg text-on-surface-variant max-w-3xl">{{ config('empresa.descripcion') }}</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 space-y-12">
        <section class="grid gap-4 md:grid-cols-2">
            <div class="card p-6">
                <span class="material-symbols-outlined text-[32px] text-primary-container">flag</span>
                <h2 class="mt-2 text-headline-md text-primary">Misión</h2>
                <p class="mt-2 text-body-lg text-on-surface-variant">{{ config('empresa.mision') }}</p>
            </div>
            <div class="card p-6">
                <span class="material-symbols-outlined text-[32px] text-primary-container">visibility</span>
                <h2 class="mt-2 text-headline-md text-primary">Visión</h2>
                <p class="mt-2 text-body-lg text-on-surface-variant">{{ config('empresa.vision') }}</p>
            </div>
        </section>

        <section>
            <p class="overline">Lo que nos guía</p>
            <h2 class="text-headline-lg text-primary">Valores</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (config('empresa.valores') as $valor)
                    <div class="card p-5">
                        <span class="material-symbols-outlined text-primary-container">{{ $valor['icon'] }}</span>
                        <p class="mt-2 text-headline-sm text-primary">{{ $valor['titulo'] }}</p>
                        <p class="mt-1 text-body-md text-on-surface-variant">{{ $valor['texto'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section>
            <p class="overline">Qué ofrecemos</p>
            <h2 class="text-headline-lg text-primary">Servicios</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach (config('empresa.servicios') as $servicio)
                    <div class="card p-5 flex gap-4">
                        <span class="material-symbols-outlined text-[32px] text-primary-container">{{ $servicio['icon'] }}</span>
                        <div>
                            <p class="text-headline-sm text-primary">{{ $servicio['titulo'] }}</p>
                            <p class="mt-1 text-body-md text-on-surface-variant">{{ $servicio['texto'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card p-6 flex flex-col md:flex-row justify-between gap-4 md:items-center">
            <div>
                <h2 class="text-headline-md text-primary">¿Tienes un proyecto en mente?</h2>
                <p class="text-body-md text-on-surface-variant">Escríbenos y un asesor técnico te contactará.</p>
            </div>
            <a href="{{ route('contact') }}" class="btn-primary">Contáctanos</a>
        </section>
    </div>
</x-app-layout>
