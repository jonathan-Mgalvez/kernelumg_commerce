@props(['product'])

{{-- Tarjeta de producto tomada del diseño "Catálogo de Productos - FerroTek" de Stitch --}}
<article class="card flex flex-col overflow-hidden">
    <div class="flex items-center justify-between px-4 py-1.5 bg-surface-container-low border-b border-line-soft">
        <span class="text-label-md text-muted">SKU: {{ $product->sku }}</span>
        @if ($product->standard)
            <span class="flex items-center gap-1.5 text-label-sm uppercase text-on-surface-variant">
                <span class="w-1.5 h-1.5 bg-primary-container"></span>{{ $product->standard }}
            </span>
        @endif
    </div>

    <a href="{{ route('catalog.show', $product) }}" class="relative block aspect-[4/2.1] bg-surface-container overflow-hidden">
        @if ($product->activeOffer)
            <span class="absolute top-2 left-2 z-10 inline-flex items-center gap-1 px-2 py-1 rounded bg-danger text-white text-label-md shadow">
                <span class="material-symbols-outlined text-[16px]">sell</span>−{{ rtrim(rtrim($product->activeOffer->discount_rate, '0'), '.') }}%
            </span>
        @endif
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="w-full h-full object-cover hover:scale-[1.02] transition-transform">
        @else
            <div class="w-full h-full flex items-center justify-center text-outline">
                <span class="material-symbols-outlined text-[56px]">{{ $product->category->icon ?? 'inventory_2' }}</span>
            </div>
        @endif
    </a>

    <div class="flex flex-col flex-1 p-4">
        <a href="{{ route('catalog.show', $product) }}" class="text-headline-sm text-primary hover:underline">{{ $product->name }}</a>
        @if ($product->subtitle)
            <p class="mt-0.5 text-label-md text-on-surface-variant">{{ $product->subtitle }}</p>
        @endif

        <div class="mt-3 flex items-center justify-between gap-2">
            <p>
                @if ($product->activeOffer)
                    <span class="block text-body-sm text-muted line-through">Q{{ number_format($product->price, 2) }}</span>
                    <span class="text-headline-md text-danger">Q{{ number_format($product->final_price, 2) }}</span>
                @else
                    <span class="text-headline-md text-primary">Q{{ number_format($product->price, 2) }}</span>
                @endif
                <span class="text-body-sm text-muted">/ {{ $product->unit }}</span>
            </p>
            @if ($product->stock_status === 'agotado')
                <span class="chip-danger">Agotado</span>
            @elseif ($product->stock_status === 'bajo')
                <span class="chip-warn">{{ number_format($product->stock) }} disp.</span>
            @else
                <span class="chip">{{ number_format($product->stock) }} {{ $product->unit === 'caja' ? 'cajas' : 'unidades' }} disp.</span>
            @endif
        </div>

        <p class="mt-2 text-body-md text-on-surface-variant line-clamp-2">{{ $product->description }}</p>

        <div class="mt-auto pt-4">
            @auth
                <div class="grid {{ auth()->user()->puedeCotizar() ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
                    @if (auth()->user()->puedeCotizar())
                        <form method="POST" action="{{ route('quote.add', $product) }}">
                            @csrf
                            <button type="submit" class="btn-secondary w-full px-2" @disabled($product->stock <= 0) title="Añadir a cotización">
                                <span class="material-symbols-outlined text-[18px]">request_quote</span>
                                Cotizar
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('cart.add', $product) }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full px-2" @disabled($product->stock <= 0) title="Añadir al carrito">
                            <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                            Comprar
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn-primary w-full">
                    <span class="material-symbols-outlined text-[18px]">login</span>
                    Inicia sesión para cotizar
                </a>
            @endauth
        </div>
    </div>
</article>
