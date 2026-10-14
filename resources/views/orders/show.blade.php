<x-app-layout>
    <x-slot:title>{{ $order->code }}</x-slot:title>
    <x-slot name="header">
        <p class="overline">Pedido</p>
        <h1 class="text-headline-lg text-primary flex items-center gap-3">{{ $order->code }} <span class="{{ $order->status_chip }}">{{ $order->status_label }}</span></h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 space-y-6">
        @include('orders._seguimiento')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @include('orders._detalle')
            </div>
            <aside class="space-y-4">
                <div class="card p-5 space-y-3 text-body-md">
                    <p class="overline">Datos</p>
                    <p><span class="text-muted">Fecha:</span> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p><span class="text-muted">Pago:</span> {{ $order->payment_label }}</p>
                    <p class="flex gap-2"><span class="material-symbols-outlined text-primary-container">local_shipping</span>{{ $order->shipping_address }}</p>
                    @if ($order->notes)<p class="p-3 bg-surface-container-low rounded">{{ $order->notes }}</p>@endif
                    @if ($order->quote)
                        <p><span class="text-muted">Cotización:</span> <a href="{{ route('quote.show', $order->quote) }}" class="text-primary-container hover:underline">{{ $order->quote->code }}</a></p>
                    @endif
                </div>

                @if ($order->status === 'pendiente' && $order->user_id === auth()->id())
                    <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('¿Cancelar el pedido {{ $order->code }}?')">
                        @csrf @method('PATCH')
                        <button class="btn-danger w-full"><span class="material-symbols-outlined text-[18px]">cancel</span> Cancelar pedido</button>
                    </form>
                    <p class="text-body-sm text-muted">Puedes cancelar mientras el pedido esté pendiente.</p>
                @endif

                <a href="{{ route('orders.index') }}" class="text-label-lg text-primary-container hover:underline">← Volver a mis pedidos</a>
            </aside>
        </div>
    </div>
</x-app-layout>
