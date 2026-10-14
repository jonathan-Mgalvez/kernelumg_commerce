@php
    $links = [
        ['route' => 'home', 'label' => 'Inicio', 'active' => 'home'],
        ['route' => 'catalog.index', 'label' => 'Catálogo de Productos', 'active' => 'catalog.*'],
        ['route' => 'offers.index', 'label' => 'Ofertas', 'active' => 'offers.*'],
        ['route' => 'about', 'label' => 'Nosotros', 'active' => 'about'],
        ['route' => 'contact', 'label' => 'Contacto', 'active' => 'contact'],
    ];
    // Visitantes, clientes y admin ven el cotizador; el gestor de inventario no cotiza.
    $cotiza = auth()->user()?->puedeCotizar() ?? true;
    if (auth()->check() && $cotiza) {
        $links[] = ['route' => 'quote.history', 'label' => 'Historial', 'active' => 'quote.history'];
    }
    $enCarrito = auth()->check() ? app(\App\Services\CarritoService::class)->lineas(auth()->user()) : 0;
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 bg-white border-b border-line">
    <div class="max-w-7xl mx-auto h-16 px-4 lg:px-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-1.5 text-primary">
                <span class="material-symbols-outlined text-[26px]">precision_manufacturing</span>
                <span class="text-headline-sm uppercase tracking-wider">FerroTek</span>
            </a>
            <nav class="hidden lg:flex items-center gap-1">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                       class="px-3 py-2 rounded text-label-lg {{ request()->routeIs($link['active']) ? 'bg-surface-container text-on-surface font-semibold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                @auth
                    @if (auth()->user()->canManageInventory())
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-3 py-2 rounded text-label-lg {{ request()->routeIs('admin.*') ? 'bg-surface-container text-on-surface font-semibold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                            {{ auth()->user()->isAdmin() ? 'Panel Admin' : 'Inventario' }}
                        </a>
                    @endif
                @endauth
            </nav>
        </div>

        <div class="flex items-center gap-2">
            @if ($cotiza)
                <a href="{{ route('quote.index') }}" class="btn-primary hidden sm:inline-flex">
                    <span class="material-symbols-outlined text-[18px]">request_quote</span>
                    Cotizador
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            @else
                <a href="{{ route('admin.products.create') }}" class="btn-primary hidden sm:inline-flex">
                    <span class="material-symbols-outlined text-[18px]">add_box</span>
                    Agregar producto
                </a>
            @endif

            @auth
                <a href="{{ route('cart.index') }}" title="Carrito de compras"
                   class="relative flex items-center justify-center w-9 h-9 rounded border {{ request()->routeIs('cart.*', 'checkout.*') ? 'border-primary-container bg-surface-container' : 'border-line hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-[20px] text-primary">shopping_cart</span>
                    @if ($enCarrito)
                        <span class="absolute -top-1.5 -right-1.5 min-w-5 h-5 px-1 rounded-full bg-warn text-white text-label-sm flex items-center justify-center">{{ $enCarrito }}</span>
                    @endif
                </a>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center justify-center w-9 h-9 rounded-full bg-primary-container text-white" title="{{ auth()->user()->name }}">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-line-soft">
                            <p class="text-label-lg text-on-surface">{{ auth()->user()->name }}</p>
                            <p class="text-body-sm text-muted">{{ auth()->user()->role_label }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">Mi cuenta</x-dropdown-link>
                        <x-dropdown-link :href="route('orders.index')">Mis pedidos</x-dropdown-link>
                        @if ($cotiza)
                            <x-dropdown-link :href="route('quote.history')">Mis cotizaciones</x-dropdown-link>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @else
                <a href="{{ route('login') }}" class="btn-secondary hidden sm:inline-flex">Iniciar sesión</a>
            @endauth

            <button @click="open = !open" class="lg:hidden w-9 h-9 flex items-center justify-center rounded border border-line" aria-label="Menú">
                <span class="material-symbols-outlined" x-text="open ? 'close' : 'menu'">menu</span>
            </button>
        </div>
    </div>

    <!-- Menú móvil -->
    <nav x-show="open" x-cloak @click.outside="open = false" class="lg:hidden border-t border-line bg-white">
        <div class="px-4 py-2 flex flex-col">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" class="py-2.5 border-b border-line-soft text-label-lg {{ request()->routeIs($link['active']) ? 'text-primary font-semibold' : 'text-on-surface-variant' }}">{{ $link['label'] }}</a>
            @endforeach
            @if ($cotiza)
                <a href="{{ route('quote.index') }}" class="py-2.5 border-b border-line-soft text-label-lg text-on-surface-variant">Cotizador</a>
            @endif
            @auth
                <a href="{{ route('cart.index') }}" class="py-2.5 border-b border-line-soft text-label-lg text-on-surface-variant">Carrito{{ $enCarrito ? " ($enCarrito)" : '' }}</a>
                <a href="{{ route('orders.index') }}" class="py-2.5 border-b border-line-soft text-label-lg text-on-surface-variant">Mis pedidos</a>
                @if (auth()->user()->canManageInventory())
                    <a href="{{ route('admin.dashboard') }}" class="py-2.5 border-b border-line-soft text-label-lg text-on-surface-variant">{{ auth()->user()->isAdmin() ? 'Panel Admin' : 'Inventario' }}</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="py-2.5 border-b border-line-soft text-label-lg text-on-surface-variant">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="py-2.5 text-label-lg text-on-surface-variant">Crear cuenta</a>
            @endauth
        </div>
    </nav>
</header>
