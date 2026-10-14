<x-app-layout>
    <x-slot:title>Carrito</x-slot:title>
    <x-slot name="header">
        <p class="overline">Compra directa</p>
        <h1 class="text-headline-lg text-primary">Carrito de Compras</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6">
        @if ($items->isEmpty())
            <div class="card p-10 text-center">
                <span class="material-symbols-outlined text-[56px] text-outline">shopping_cart</span>
                <h2 class="mt-2 text-headline-md text-primary">Tu carrito está vacío</h2>
                <p class="mt-1 text-body-md text-muted">Agrega productos desde el catálogo o aprovecha las ofertas vigentes.</p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    <a href="{{ route('catalog.index') }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">inventory_2</span> Ver catálogo</a>
                    <a href="{{ route('offers.index') }}" class="btn-secondary"><span class="material-symbols-outlined text-[18px]">sell</span> Ver ofertas</a>
                </div>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 card overflow-x-auto">
                    <table class="table-ft min-w-[620px]">
                        <thead><tr><th>Producto</th><th class="!text-right">Precio</th><th>Cantidad</th><th class="!text-right">Importe</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($items as $item)
                                @php($linea = $lineas[$item->product_id])
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 shrink-0 rounded bg-surface-container overflow-hidden flex items-center justify-center">
                                                @if ($item->product->image_url)<img src="{{ $item->product->image_url }}" alt="" class="w-full h-full object-cover">
                                                @else<span class="material-symbols-outlined text-outline">{{ $item->product->category->icon ?? 'inventory_2' }}</span>@endif
                                            </div>
                                            <div>
                                                <a href="{{ route('catalog.show', $item->product) }}" class="font-semibold text-primary hover:underline">{{ $item->product->name }}</a>
                                                <p class="text-body-sm text-muted">{{ $item->product->sku }} · {{ number_format($item->product->stock) }} disponibles</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="num">
                                        @if ($linea['discount_rate'] > 0)
                                            <span class="block text-body-sm text-muted line-through">Q{{ number_format($linea['unit_price'], 2) }}</span>
                                            <span class="text-danger font-semibold">Q{{ number_format($item->product->final_price, 2) }}</span>
                                        @else
                                            Q{{ number_format($linea['unit_price'], 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('cart.update', $item) }}">
                                            @csrf @method('PATCH')
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="{{ $item->product->stock }}"
                                                   class="input h-8 w-24" aria-label="Cantidad" onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td class="num">
                                        <p class="font-semibold">Q{{ number_format($linea['amount'], 2) }}</p>
                                        @if ($linea['discount_rate'] > 0)
                                            <span class="chip-danger">Oferta −{{ rtrim(rtrim(number_format($linea['discount_rate'], 2), '0'), '.') }}%</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('cart.remove', $item) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger btn-sm" title="Quitar"><span class="material-symbols-outlined text-[16px]">delete</span></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">
                        <a href="{{ route('catalog.index') }}" class="text-label-lg text-primary-container hover:underline">← Seguir comprando</a>
                    </div>
                </div>

                <aside class="space-y-4">
                    @include('cart._totales')
                    <a href="{{ route('checkout.index') }}" class="btn-primary w-full h-11">
                        <span class="material-symbols-outlined text-[18px]">lock</span> Proceder al pago
                    </a>
                    @if (auth()->user()->puedeCotizar())
                        <div class="card p-4 text-body-sm text-muted flex gap-2">
                            <span class="material-symbols-outlined text-primary-container">request_quote</span>
                            <p>¿Compras por volumen? <a href="{{ route('quote.index') }}" class="text-primary-container hover:underline">Arma una cotización</a> y obtén hasta {{ max(config('empresa.descuentos_volumen')) }}% de descuento por línea.</p>
                        </div>
                    @endif
                </aside>
            </div>
        @endif
    </div>
</x-app-layout>
