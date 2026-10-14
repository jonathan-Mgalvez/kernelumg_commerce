<x-app-layout>
    <x-slot:title>Ofertas</x-slot:title>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="overline">Precios especiales por tiempo limitado</p>
                <h1 class="text-headline-lg text-primary flex items-center gap-2"><span class="material-symbols-outlined text-danger">sell</span> Ofertas vigentes</h1>
            </div>
            <p class="text-body-md text-muted">El descuento se aplica automáticamente al comprar desde el carrito.</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6">
        @if ($products->isEmpty())
            <div class="card p-10 text-center">
                <span class="material-symbols-outlined text-[56px] text-outline">sell</span>
                <h2 class="mt-2 text-headline-md text-primary">No hay ofertas activas en este momento</h2>
                <p class="mt-1 text-body-md text-muted">Vuelve pronto o revisa el catálogo completo.</p>
                <a href="{{ route('catalog.index') }}" class="btn-primary mt-5">Ver catálogo</a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <div class="flex flex-col">
                        <p class="mb-1 text-label-md text-danger flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">schedule</span>
                            {{ $product->activeOffer->title ?: 'Oferta' }} · termina {{ $product->activeOffer->ends_at->diffForHumans() }}
                        </p>
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
