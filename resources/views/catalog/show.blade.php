<x-app-layout>
    <x-slot:title>{{ $product->name }}</x-slot:title>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6">
        <nav class="text-label-md text-muted mb-4">
            <a href="{{ route('catalog.index') }}" class="hover:underline">Catálogo</a> /
            <a href="{{ route('catalog.index', ['categoria' => $product->category->slug]) }}" class="hover:underline">{{ $product->category->name }}</a> /
            <span class="text-on-surface">{{ $product->sku }}</span>
        </nav>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card overflow-hidden aspect-[4/3] bg-surface-container">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-outline">
                        <span class="material-symbols-outlined text-[96px]">{{ $product->category->icon }}</span>
                    </div>
                @endif
            </div>

            <div class="card p-6 flex flex-col">
                <div class="flex flex-wrap gap-2">
                    <span class="chip">SKU: {{ $product->sku }}</span>
                    @if ($product->standard)<span class="chip-info">{{ $product->standard }}</span>@endif
                </div>
                <h1 class="mt-3 text-headline-lg text-primary">{{ $product->name }}</h1>
                <p class="text-label-md text-on-surface-variant">{{ $product->subtitle }}</p>

                @if ($offer = $product->activeOffer)
                    <div class="mt-4 flex flex-wrap items-center gap-2 p-3 rounded border border-danger/30 bg-danger/5">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-danger text-white text-label-md">
                            <span class="material-symbols-outlined text-[16px]">sell</span>−{{ rtrim(rtrim($offer->discount_rate, '0'), '.') }}%
                        </span>
                        <span class="text-label-lg text-danger">{{ $offer->title ?: 'Oferta especial' }}</span>
                        <span class="text-body-sm text-muted">· válida hasta el {{ $offer->ends_at->format('d/m/Y') }} en compras desde el carrito</span>
                    </div>
                    <p class="mt-3">
                        <span class="text-body-lg text-muted line-through mr-2">Q{{ number_format($product->price, 2) }}</span>
                        <span class="text-display-lg text-danger">Q{{ number_format($product->final_price, 2) }}</span>
                        <span class="text-body-md text-muted">/ {{ $product->unit }} · IVA no incluido</span>
                    </p>
                @else
                    <p class="mt-4"><span class="text-display-lg text-primary">Q{{ number_format($product->price, 2) }}</span> <span class="text-body-md text-muted">/ {{ $product->unit }} · IVA no incluido</span></p>
                @endif

                <p class="mt-4 text-body-lg text-on-surface-variant">{{ $product->description }}</p>

                <table class="table-ft mt-5 border border-line">
                    <tbody>
                        <tr><td class="text-muted w-40">Categoría</td><td>{{ $product->category->name }}</td></tr>
                        <tr><td class="text-muted">Norma / Especificación</td><td>{{ $product->standard ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Disponibilidad</td><td>
                            @if ($product->stock_status === 'agotado')<span class="chip-danger">Agotado</span>
                            @elseif ($product->stock_status === 'bajo')<span class="chip-warn">Stock bajo · {{ number_format($product->stock) }}</span>
                            @else<span class="chip-ok">{{ number_format($product->stock) }} disponibles</span>@endif
                        </td></tr>
                        <tr><td class="text-muted">Descuento por volumen</td><td>
                            @foreach (config('empresa.descuentos_volumen') as $min => $pct)
                                <span class="block">Desde {{ $min }} {{ $product->unit === 'caja' ? 'cajas' : 'unidades' }}: −{{ $pct }}%</span>
                            @endforeach
                        </td></tr>
                    </tbody>
                </table>

                <div class="mt-auto pt-6">
                    @auth
                        {{-- Una sola cantidad para los dos botones: cada botón envía el formulario a su ruta --}}
                        <form method="POST" action="{{ route('cart.add', $product) }}" class="flex flex-wrap gap-2">
                            @csrf
                            <input type="number" name="quantity" value="1" min="1" max="100000" class="input w-24" aria-label="Cantidad">
                            @if (auth()->user()->puedeCotizar())
                                <button formaction="{{ route('quote.add', $product) }}" class="btn-secondary flex-1 min-w-36" @disabled($product->stock <= 0)>
                                    <span class="material-symbols-outlined text-[18px]">request_quote</span> Añadir a Cotización
                                </button>
                            @endif
                            <button class="btn-primary flex-1 min-w-36" @disabled($product->stock <= 0)>
                                <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span> Añadir al Carrito
                            </button>
                        </form>
                        @if (auth()->user()->puedeCotizar())
                            <p class="mt-2 text-body-sm text-muted">¿Compra grande? Cotiza para obtener descuento por volumen; el carrito aplica las ofertas vigentes.</p>
                        @else
                            <a href="{{ route('admin.products.edit', $product) }}" class="mt-3 btn-secondary w-full">
                                <span class="material-symbols-outlined text-[18px]">edit</span> Editar producto en inventario
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-primary w-full">Inicia sesión para cotizar o comprar</a>
                    @endauth
                </div>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <h2 class="mt-10 text-headline-md text-primary">Productos relacionados</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
