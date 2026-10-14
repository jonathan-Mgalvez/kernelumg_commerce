<footer class="mt-12 bg-surface-container-low border-t border-line">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-8 grid gap-8 md:grid-cols-3">
        <div>
            <div class="flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined">precision_manufacturing</span>
                <span class="text-headline-sm uppercase tracking-wider">FerroTek</span>
            </div>
            <p class="mt-2 text-body-md text-muted">{{ config('empresa.eslogan') }}</p>
        </div>
        <div>
            <p class="overline mb-2">Navegación</p>
            <ul class="space-y-1 text-body-md">
                <li><a class="hover:underline" href="{{ route('catalog.index') }}">Catálogo de productos</a></li>
                <li><a class="hover:underline" href="{{ route('about') }}">Nosotros</a></li>
                @if (auth()->user()?->puedeCotizar() ?? true)
                    <li><a class="hover:underline" href="{{ route('quote.index') }}">Cotizador en línea</a></li>
                @endif
                <li><a class="hover:underline" href="{{ route('contact') }}">Contacto</a></li>
            </ul>
        </div>
        <div>
            <p class="overline mb-2">Contacto</p>
            <ul class="space-y-1 text-body-md text-on-surface-variant">
                <li class="flex gap-2"><span class="material-symbols-outlined text-[18px]">location_on</span>{{ config('empresa.contacto.direccion') }}</li>
                <li class="flex gap-2"><span class="material-symbols-outlined text-[18px]">call</span>{{ config('empresa.contacto.telefono') }}</li>
                <li class="flex gap-2"><span class="material-symbols-outlined text-[18px]">mail</span>{{ config('empresa.contacto.correo') }}</li>
            </ul>
        </div>
    </div>
    <div class="border-t border-line">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 py-4 flex flex-col sm:flex-row gap-2 justify-between text-label-md text-muted">
            <span>Normativa ISO 9001 / DIN EN 10204</span>
            <span>© {{ date('Y') }} FerroTek · Proyecto académico, empresa ficticia</span>
        </div>
    </div>
</footer>
