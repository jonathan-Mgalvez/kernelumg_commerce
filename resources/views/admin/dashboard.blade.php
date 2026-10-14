<x-admin-layout title="Resumen">
    <x-slot name="actions">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.offers.create') }}" class="btn-secondary"><span class="material-symbols-outlined text-[18px]">sell</span> Nueva oferta</a>
            <a href="{{ route('admin.products.create') }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">add_box</span> Agregar producto</a>
        </div>
    </x-slot>

    <div class="grid gap-4 grid-cols-2 xl:grid-cols-4">
        @foreach (array_filter([
            ['shopping_bag', 'Pedidos del mes', number_format($stats['pedidos_mes']), route('admin.orders.index'), false],
            ['point_of_sale', 'Ventas del mes', 'Q'.number_format($stats['ventas_mes'], 2), route('admin.orders.index'), false],
            ['local_shipping', 'Pedidos por despachar', number_format($stats['pedidos_pendientes']), route('admin.orders.index', ['estado' => 'pendiente']), false],
            ['sell', 'Ofertas vigentes', number_format($stats['ofertas']), route('admin.offers.index', ['estado' => 'vigente']), false],
            ['inventory_2', 'Productos', number_format($stats['productos']), route('admin.products.index'), false],
            ['warning', 'Stock bajo', number_format($stats['stock_bajo']), route('admin.inventory', ['estado' => 'bajo']), false],
            ['request_quote', 'Cotizaciones del mes', number_format($stats['cotizaciones_mes']), route('admin.quotes.index'), false],
            ['payments', 'Monto cotizado (mes)', 'Q'.number_format($stats['monto_mes'], 2), route('admin.quotes.index'), false],
            ['pending_actions', 'Pendientes de revisar', number_format($stats['pendientes']), route('admin.quotes.index', ['estado' => 'enviada']), false],
            ['mail', 'Mensajes sin leer', number_format($stats['mensajes']), route('admin.messages.index'), true],
            ['group', 'Clientes registrados', number_format($stats['clientes']), route('admin.users.index'), true],
        ], fn ($c) => ! $c[4] || auth()->user()->isAdmin()) as [$icon, $label, $value, $url])
            <a href="{{ $url }}" class="card p-4 hover:border-primary-container transition-colors">
                <div class="flex items-center justify-between">
                    <p class="overline">{{ $label }}</p>
                    <span class="material-symbols-outlined text-primary-container">{{ $icon }}</span>
                </div>
                <p class="mt-2 text-headline-lg text-primary num !text-left">{{ $value }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="card xl:col-span-2">
            <div class="flex items-center justify-between p-4">
                <h2 class="text-headline-sm text-primary">Últimos pedidos</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-label-md text-primary-container hover:underline">Ver todos</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-ft">
                    <thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th class="!text-right">Total</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($pedidos as $o)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $o) }}" class="font-semibold text-primary hover:underline">{{ $o->code }}</a></td>
                                <td>{{ $o->user->company ?? $o->user->name }}</td>
                                <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                                <td class="num">Q{{ number_format($o->total, 2) }}</td>
                                <td><span class="{{ $o->status_chip }}">{{ $o->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-6">Sin pedidos todavía.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="flex items-center justify-between p-4">
                <h2 class="text-headline-sm text-primary">Últimas cotizaciones</h2>
                <a href="{{ route('admin.quotes.index') }}" class="text-label-md text-primary-container hover:underline">Ver todas</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-ft">
                    <thead><tr><th>Código</th><th>Cliente</th><th class="!text-right">Total</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($ultimas as $q)
                            <tr>
                                <td><a href="{{ route('admin.quotes.show', $q) }}" class="font-semibold text-primary hover:underline">{{ $q->code }}</a></td>
                                <td>{{ $q->user->company ?? $q->user->name }}</td>
                                <td class="num">Q{{ number_format($q->total, 2) }}</td>
                                <td><span class="{{ $q->status_chip }}">{{ $q->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-6">Sin cotizaciones.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="flex items-center justify-between p-4">
                <h2 class="text-headline-sm text-primary">Alertas de reabastecimiento</h2>
                <a href="{{ route('admin.inventory', ['estado' => 'bajo']) }}" class="text-label-md text-primary-container hover:underline">Ver inventario</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-ft">
                    <thead><tr><th>SKU</th><th>Producto</th><th class="!text-right">Stock</th><th class="!text-right">Mínimo</th></tr></thead>
                    <tbody>
                        @forelse ($alertas as $p)
                            <tr>
                                <td class="text-muted">{{ $p->sku }}</td>
                                <td>{{ $p->name }}</td>
                                <td class="num {{ $p->stock == 0 ? 'text-danger' : 'text-warn' }} font-semibold">{{ number_format($p->stock) }}</td>
                                <td class="num">{{ number_format($p->min_stock) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-6">Todo el inventario está sobre el mínimo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
