<x-admin-layout :title="'Pedido '.$order->code">
    <div class="space-y-6">
        @include('orders._seguimiento')

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('orders._detalle')
            </div>
            <aside class="space-y-4">
                @if ($order->status !== 'cancelado')
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="card p-4 space-y-3">
                        @csrf @method('PATCH')
                        <label class="label" for="status">Estado del pedido</label>
                        <select id="status" name="status" class="input">
                            @foreach (\App\Models\Order::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn-primary w-full" onclick="return this.form.status.value !== 'cancelado' || confirm('¿Cancelar el pedido? El stock se devolverá al inventario.')">Actualizar estado</button>
                        <p class="text-body-sm text-muted">El cliente recibe un correo con cada cambio. Cancelar devuelve el stock.</p>
                    </form>
                @endif

                <div class="card p-4 space-y-2 text-body-md">
                    <p class="overline">Cliente</p>
                    <p class="font-semibold">{{ $order->user->name }}</p>
                    <p>{{ $order->user->company }}</p>
                    <p class="text-muted">{{ $order->user->email }} · {{ $order->user->phone }}</p>
                    <p class="overline pt-2">Entrega</p>
                    <p>{{ $order->shipping_address }}</p>
                    <p><span class="text-muted">Pago:</span> {{ $order->payment_label }}</p>
                    @if ($order->notes)<p class="p-3 bg-surface-container-low rounded">{{ $order->notes }}</p>@endif
                    @if ($order->quote)
                        <p><span class="text-muted">Cotización:</span> <a href="{{ route('admin.quotes.show', $order->quote) }}" class="text-primary-container hover:underline">{{ $order->quote->code }}</a></p>
                    @endif
                    <p class="text-muted pt-2">Creado {{ $order->created_at->format('d/m/Y H:i') }} · actualizado {{ $order->updated_at->diffForHumans() }}</p>
                </div>
            </aside>
        </div>
    </div>
</x-admin-layout>
