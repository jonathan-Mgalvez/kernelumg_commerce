<x-app-layout>
    <x-slot:title>Pedido confirmado</x-slot:title>

    <div class="max-w-3xl mx-auto px-4 lg:px-6 py-10 space-y-6">
        <div class="card p-8 text-center">
            <span class="inline-flex w-16 h-16 rounded-full bg-ok/10 text-ok items-center justify-center">
                <span class="material-symbols-outlined text-[40px]">task_alt</span>
            </span>
            <h1 class="mt-3 text-headline-lg text-primary">¡Pedido confirmado!</h1>
            <p class="mt-1 text-body-lg text-on-surface-variant">Tu número de pedido es <strong class="text-primary">{{ $order->code }}</strong>.</p>
            <p class="mt-1 text-body-md text-muted">Te enviamos la confirmación a {{ auth()->user()->email }}. Forma de pago: {{ $order->payment_label }}.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <a href="{{ route('orders.show', $order) }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">local_shipping</span> Ver seguimiento</a>
                <a href="{{ route('catalog.index') }}" class="btn-secondary">Seguir comprando</a>
            </div>
        </div>

        @include('orders._detalle')
    </div>
</x-app-layout>
