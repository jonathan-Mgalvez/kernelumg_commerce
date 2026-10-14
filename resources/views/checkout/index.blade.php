<x-app-layout>
    <x-slot:title>Finalizar compra</x-slot:title>
    <x-slot name="header">
        <p class="overline">Paso 2 de 2</p>
        <h1 class="text-headline-lg text-primary">Finalizar compra</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6">
        <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-6 lg:grid-cols-3">
            @csrf
            <div class="lg:col-span-2 space-y-6">
                <section class="card p-5 space-y-4">
                    <h2 class="text-headline-sm text-primary flex items-center gap-2"><span class="material-symbols-outlined">local_shipping</span> Entrega y pago</h2>
                    @include('checkout._pago', ['direccion' => $direccion])
                    <p class="text-body-sm text-muted flex gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">info</span>
                        El stock se reserva al confirmar. Para transferencia o depósito recibirás los datos bancarios por correo.
                    </p>
                </section>

                <section class="card">
                    <h2 class="p-4 text-headline-sm text-primary">Productos ({{ $items->count() }})</h2>
                    <table class="table-ft">
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>
                                        <p class="font-semibold">{{ $item->quantity }}x {{ $item->product->name }}</p>
                                        <p class="text-body-sm text-muted">{{ $item->product->sku }}
                                            @if ($lineas[$item->product_id]['discount_rate'] > 0) · <span class="text-danger">oferta −{{ rtrim(rtrim(number_format($lineas[$item->product_id]['discount_rate'], 2), '0'), '.') }}%</span>@endif
                                        </p>
                                    </td>
                                    <td class="num font-semibold">Q{{ number_format($lineas[$item->product_id]['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4"><a href="{{ route('cart.index') }}" class="text-label-lg text-primary-container hover:underline">← Modificar carrito</a></div>
                </section>
            </div>

            <aside class="space-y-4">
                @include('cart._totales')
                <button class="btn-primary w-full h-11">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span> Confirmar pedido · Q{{ number_format($total, 2) }}
                </button>
            </aside>
        </form>
    </div>
</x-app-layout>
