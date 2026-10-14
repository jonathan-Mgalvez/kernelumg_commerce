<x-app-layout>
    <x-slot:title>Contacto</x-slot:title>
    <x-slot name="header">
        <p class="overline">Atención a clientes</p>
        <h1 class="text-display-lg text-primary">Contacto</h1>
        <p class="mt-1 text-body-lg text-on-surface-variant">Escríbenos para consultas de disponibilidad, precios por volumen o asesoría técnica.</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('contact.store') }}" class="card p-6 lg:col-span-2 grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label class="label" for="name">Nombre completo *</label>
                <input class="input" id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="100">
            </div>
            <div>
                <label class="label" for="email">Correo electrónico *</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required>
            </div>
            <div>
                <label class="label" for="phone">Teléfono</label>
                <input class="input" id="phone" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" maxlength="30">
            </div>
            <div>
                <label class="label" for="subject">Asunto *</label>
                <input class="input" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="150">
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="message">Mensaje *</label>
                <textarea class="input h-36 py-2" id="message" name="message" required maxlength="2000">{{ old('message') }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <button class="btn-primary">
                    <span class="material-symbols-outlined text-[18px]">send</span> Enviar mensaje
                </button>
            </div>
        </form>

        <aside class="space-y-4">
            <div class="card p-5 space-y-3 text-body-md">
                <p class="overline">Datos de contacto</p>
                <p class="flex gap-2"><span class="material-symbols-outlined text-primary-container">location_on</span>{{ config('empresa.contacto.direccion') }}</p>
                <p class="flex gap-2"><span class="material-symbols-outlined text-primary-container">call</span>{{ config('empresa.contacto.telefono') }}</p>
                <p class="flex gap-2"><span class="material-symbols-outlined text-primary-container">mail</span>{{ config('empresa.contacto.correo') }}</p>
                <p class="flex gap-2"><span class="material-symbols-outlined text-primary-container">schedule</span>{{ config('empresa.contacto.horario') }}</p>
                <a href="https://wa.me/{{ config('empresa.contacto.whatsapp') }}" target="_blank" rel="noopener" class="btn-secondary w-full">
                    <span class="material-symbols-outlined text-[18px]">chat</span> Escribir por WhatsApp
                </a>
            </div>
            <div class="card overflow-hidden">
                <iframe title="Mapa" class="w-full h-56 border-0" loading="lazy"
                        src="https://maps.google.com/maps?q=Zona%2012%2C%20Guatemala&z=14&output=embed"></iframe>
            </div>
        </aside>
    </div>
</x-app-layout>
