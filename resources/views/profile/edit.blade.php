<x-app-layout>
    <x-slot:title>Mi cuenta</x-slot:title>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="overline">Mi cuenta · {{ $user->role_label }}</p>
                <h1 class="text-headline-lg text-primary">Hola, {{ $user->name }}</h1>
                @if ($user->company)<p class="text-body-md text-muted">{{ $user->company }}</p>@endif
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($user->puedeCotizar())
                    <a href="{{ route('quote.index') }}" class="btn-secondary"><span class="material-symbols-outlined text-[18px]">request_quote</span> Cotizador</a>
                @else
                    <a href="{{ route('admin.products.index') }}" class="btn-secondary"><span class="material-symbols-outlined text-[18px]">inventory_2</span> Inventario</a>
                @endif
                <a href="{{ route('catalog.index') }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">add_shopping_cart</span> Comprar</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 space-y-6">
        {{-- Panel del cliente --}}
        <div class="grid gap-4 grid-cols-2 xl:grid-cols-4">
            @foreach (array_filter([
                $user->puedeCotizar() ? ['request_quote', 'Cotizaciones enviadas', number_format($resumen['cotizaciones']), route('quote.history')] : null,
                $user->puedeCotizar() ? ['verified', 'Cotizaciones aprobadas', number_format($resumen['aprobadas']), route('quote.history', ['estado' => 'aprobada'])] : null,
                ['local_shipping', 'Pedidos', number_format($resumen['pedidos']), route('orders.index')],
                ['payments', 'Total comprado', 'Q'.number_format($resumen['comprado'], 2), route('orders.index')],
            ]) as [$icon, $label, $value, $url])
                <a href="{{ $url }}" class="card p-4 hover:border-primary-container transition-colors">
                    <div class="flex items-center justify-between">
                        <p class="overline">{{ $label }}</p>
                        <span class="material-symbols-outlined text-primary-container">{{ $icon }}</span>
                    </div>
                    <p class="mt-2 text-headline-lg text-primary num !text-left">{{ $value }}</p>
                </a>
            @endforeach
        </div>

        @if ($ultimoPedido)
            <div class="card p-4 flex flex-wrap items-center gap-4">
                <span class="material-symbols-outlined text-primary-container text-[32px]">package_2</span>
                <div class="flex-1">
                    <p class="overline">Último pedido</p>
                    <p class="text-label-lg">{{ $ultimoPedido->code }} · Q{{ number_format($ultimoPedido->total, 2) }} · {{ $ultimoPedido->created_at->format('d/m/Y') }}</p>
                </div>
                <span class="{{ $ultimoPedido->status_chip }}">{{ $ultimoPedido->status_label }}</span>
                <a href="{{ route('orders.show', $ultimoPedido) }}" class="btn-secondary">Seguimiento</a>
            </div>
        @endif

        {{-- Formularios de Breeze --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card p-5 sm:p-6">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div class="card p-5 sm:p-6">
                @include('profile.partials.update-password-form')
            </div>
        </div>
        <div class="card p-5 sm:p-6 max-w-3xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
