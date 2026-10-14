@props(['title' => 'Panel'])

@php
    $esAdmin = auth()->user()->isAdmin();
    $pending = \App\Models\Quote::where('status', 'enviada')->count();
    $pedidos = \App\Models\Order::where('status', 'pendiente')->count();
    $unread = $esAdmin ? \App\Models\ContactMessage::where('read', false)->count() : 0;
    // [ruta, patrón activo, ícono, texto, contador, solo admin]
    $menu = array_filter([
        ['admin.dashboard', 'admin.dashboard', 'dashboard', 'Resumen', null, false],
        ['admin.products.index', 'admin.products.*', 'inventory_2', 'Productos', null, false],
        ['admin.categories.index', 'admin.categories.*', 'category', 'Categorías', null, false],
        ['admin.inventory', 'admin.inventory', 'warehouse', 'Lotes y Almacén', null, false],
        ['admin.offers.index', 'admin.offers.*', 'sell', 'Ofertas', null, false],
        ['admin.orders.index', 'admin.orders.*', 'local_shipping', 'Pedidos', $pedidos, false],
        ['admin.quotes.index', 'admin.quotes.*', 'request_quote', 'Cotizaciones', $pending, false],
        ['admin.messages.index', 'admin.messages.*', 'mail', 'Mensajes', $unread, true],
        ['admin.users.index', 'admin.users.*', 'group', 'Usuarios', null, true],
        ['admin.audits.index', 'admin.audits.*', 'history', 'Auditoría', null, true],
    ], fn ($item) => $esAdmin || ! $item[5]);
@endphp

<x-app-layout>
    <x-slot:title>{{ $title }} · Admin</x-slot:title>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside>
            <p class="overline mb-2 px-2">{{ $esAdmin ? 'Panel administrativo' : 'Gestión de inventario' }}</p>
            <nav class="flex lg:flex-col gap-1 overflow-x-auto pb-2 lg:pb-0">
                @foreach ($menu as [$route, $pattern, $icon, $label, $badge])
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-2 whitespace-nowrap px-3 py-2 rounded text-label-lg {{ request()->routeIs($pattern) ? 'bg-primary-container text-white' : 'text-on-surface-variant hover:bg-surface-container' }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                        <span class="flex-1">{{ $label }}</span>
                        @if ($badge)
                            <span class="min-w-5 px-1.5 rounded bg-warn text-white text-label-sm text-center">{{ $badge }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <h1 class="text-headline-lg text-primary">{{ $title }}</h1>
                {{ $actions ?? '' }}
            </div>
            {{ $slot }}
        </div>
    </div>
</x-app-layout>
