<x-app-layout>
    <x-slot:title>Mis pedidos</x-slot:title>
    <x-slot name="header">
        <p class="overline">Mi cuenta</p>
        <h1 class="text-headline-lg text-primary">Mis Pedidos</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 space-y-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="estado" class="input w-auto pr-8" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                @foreach (\App\Models\Order::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('estado') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ route('catalog.index') }}" class="btn-primary ml-auto">
                <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span> Nueva compra
            </a>
        </form>

        <div class="card overflow-x-auto">
            <table class="table-ft min-w-[640px]">
                <thead><tr><th>Pedido</th><th>Fecha</th><th class="!text-right">Productos</th><th class="!text-right">Total</th><th>Pago</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="font-semibold text-primary">{{ $order->code }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="num">{{ $order->items_count }}</td>
                            <td class="num font-semibold">Q{{ number_format($order->total, 2) }}</td>
                            <td class="text-body-sm">{{ $order->payment_label }}</td>
                            <td><span class="{{ $order->status_chip }}">{{ $order->status_label }}</span></td>
                            <td class="text-right"><a href="{{ route('orders.show', $order) }}" class="btn-secondary btn-sm">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-muted">
                            <span class="material-symbols-outlined text-[36px] block">local_shipping</span>
                            Todavía no has hecho pedidos.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</x-app-layout>
