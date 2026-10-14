<x-app-layout>
    <x-slot:title>Inicio</x-slot:title>

    <!-- Hero -->
    <section class="relative bg-primary-container text-white overflow-hidden">
        <img src="{{ asset('img/productos/varilla-corrugada.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-25">
        <div class="relative max-w-7xl mx-auto px-4 lg:px-6 py-16 lg:py-24">
            <p class="text-label-sm uppercase text-primary-fixed">Suministros directos · Guatemala</p>
            <h1 class="mt-3 text-[2rem] sm:text-[2.75rem] leading-tight font-semibold tracking-tight max-w-3xl">
                Materiales industriales certificados, cotizados en minutos.
            </h1>
            <p class="mt-4 text-body-lg text-primary-fixed max-w-2xl">{{ config('empresa.descripcion') }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('catalog.index') }}" class="btn bg-white text-primary hover:bg-primary-fixed">
                    <span class="material-symbols-outlined text-[18px]">inventory_2</span> Ver catálogo
                </a>
                @if (auth()->user()?->puedeCotizar() ?? true)
                    <a href="{{ route('quote.index') }}" class="btn border border-white/60 text-white hover:bg-white/10">
                        <span class="material-symbols-outlined text-[18px]">request_quote</span> Crear cotización
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Indicadores -->
    <section class="bg-white border-b border-line">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 grid grid-cols-2 md:grid-cols-4 divide-x divide-line-soft">
            @foreach ([['+12,000', 'SKUs en inventario'], ['24 h', 'Entrega en área metropolitana'], ['ISO 9001', 'Materiales certificados'], ['+350', 'Constructoras atendidas']] as [$valor, $texto])
                <div class="py-6 px-4">
                    <p class="text-headline-lg text-primary">{{ $valor }}</p>
                    <p class="text-label-md uppercase text-muted">{{ $texto }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Categorías -->
    <section class="max-w-7xl mx-auto px-4 lg:px-6 pt-12">
        <p class="overline">Líneas de producto</p>
        <h2 class="text-headline-lg text-primary">Categorías</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $category)
                <a href="{{ route('catalog.index', ['categoria' => $category->slug]) }}" class="card p-5 hover:border-primary-container transition-colors group">
                    <span class="material-symbols-outlined text-[32px] text-primary-container">{{ $category->icon }}</span>
                    <p class="mt-3 text-headline-sm text-primary group-hover:underline">{{ $category->name }}</p>
                    <p class="mt-1 text-body-md text-on-surface-variant">{{ $category->description }}</p>
                    <p class="mt-3 text-label-sm uppercase text-muted">{{ $category->products_count }} productos</p>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Destacados -->
    <section class="max-w-7xl mx-auto px-4 lg:px-6 pt-12">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="overline">Tarifa vigente {{ now()->year }}</p>
                <h2 class="text-headline-lg text-primary">Productos destacados</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-label-lg text-primary-container hover:underline whitespace-nowrap">Ver todo →</a>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    <!-- Llamado a cotizar -->
    <section class="max-w-7xl mx-auto px-4 lg:px-6 pt-12">
        <div class="card p-6 lg:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-headline-md text-primary">¿Compras por volumen?</h2>
                <p class="mt-1 text-body-md text-on-surface-variant max-w-2xl">
                    Arma tu cotización en línea y obtén descuentos automáticos desde 50 unidades por producto.
                    Flete bonificado en pedidos mayores a Q{{ number_format(config('empresa.envio_gratis_desde')) }}.
                </p>
            </div>
            @if (auth()->user()?->isInventario())
                <a href="{{ route('admin.products.index') }}" class="btn-primary whitespace-nowrap">Ir al inventario</a>
            @else
                <a href="{{ route(auth()->check() ? 'quote.index' : 'register') }}" class="btn-primary whitespace-nowrap">
                    {{ auth()->check() ? 'Ir al cotizador' : 'Crear cuenta gratis' }}
                </a>
            @endif
        </div>
    </section>
</x-app-layout>
