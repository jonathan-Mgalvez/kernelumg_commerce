<x-app-layout>
    <x-slot:title>{{ $quote->code }}</x-slot:title>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="overline">Cotización</p>
                <h1 class="text-headline-lg text-primary flex items-center gap-3">{{ $quote->code }} <span class="{{ $quote->status_chip }}">{{ $quote->status_label }}</span></h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('quote.pdf', $quote) }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> Descargar PDF</a>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-secondary"><span class="material-symbols-outlined text-[18px]">chat</span> WhatsApp</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @include('quotes._summary', ['quote' => $quote, 'editable' => false])
        </div>
        <aside class="card p-5 space-y-3 text-body-md h-fit">
            <p class="overline">Datos</p>
            <p><span class="text-muted">Cliente:</span> {{ $quote->user->name }}{{ $quote->user->company ? ' ('.$quote->user->company.')' : '' }}</p>
            <p><span class="text-muted">Fecha:</span> {{ $quote->created_at->format('d/m/Y H:i') }}</p>
            <p><span class="text-muted">Válida hasta:</span> {{ $quote->valid_until?->format('d/m/Y') }}</p>
            <p class="flex gap-2"><span class="material-symbols-outlined text-primary-container">local_shipping</span>{{ $quote->delivery_address ?? '—' }}</p>
            @if ($quote->notes)
                <p class="p-3 bg-surface-container-low rounded">{{ $quote->notes }}</p>
            @endif
            @if (auth()->user()->puedeCotizar())
                <a href="{{ route('quote.history') }}" class="text-label-lg text-primary-container hover:underline">← Volver al historial</a>
            @else
                <a href="{{ route('admin.quotes.show', $quote) }}" class="text-label-lg text-primary-container hover:underline">← Volver al panel</a>
            @endif
        </aside>

        @if ($quote->order)
            <div class="lg:col-span-3 card p-4 flex flex-wrap items-center gap-3 border-ok/40 bg-ok/5">
                <span class="material-symbols-outlined text-ok text-[28px]">task_alt</span>
                <p class="flex-1 text-body-md">Esta cotización ya se convirtió en el pedido <strong>{{ $quote->order->code }}</strong> ({{ $quote->order->status_label }}).</p>
                <a href="{{ route('orders.show', $quote->order) }}" class="btn-secondary">Ver pedido</a>
            </div>
        @elseif ($quote->user_id === auth()->id() && $quote->puedeConvertirse())
            <form method="POST" action="{{ route('quote.order', $quote) }}" class="lg:col-span-3 card p-5 space-y-4 border-primary-container">
                @csrf
                <div class="flex flex-wrap items-center gap-3">
                    <span class="material-symbols-outlined text-primary-container text-[32px]">shopping_cart_checkout</span>
                    <div class="flex-1">
                        <h2 class="text-headline-sm text-primary">¡Tu cotización fue aprobada! Conviértela en pedido</h2>
                        <p class="text-body-sm text-muted">Se respetan los precios y descuentos cotizados (total Q{{ number_format($quote->total, 2) }}) hasta el {{ $quote->valid_until?->format('d/m/Y') }}, sujeto a stock.</p>
                    </div>
                </div>
                @include('checkout._pago', ['direccion' => $quote->delivery_address])
                <button class="btn-primary"><span class="material-symbols-outlined text-[18px]">check_circle</span> Confirmar pedido</button>
            </form>
        @endif
    </div>
</x-app-layout>
