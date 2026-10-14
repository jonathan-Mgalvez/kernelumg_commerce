<x-app-layout>
    <x-slot:title>Catálogo</x-slot:title>
    <x-slot name="header">
        <p class="overline">Suministros directos / Tarifa vigente {{ now()->year }}</p>
        <h1 class="text-headline-lg text-primary">Catálogo de Especificaciones Técnicas</h1>
        <p class="mt-1 text-body-md text-on-surface-variant max-w-2xl">
            Inventario en tiempo real con certificados de calidad de colada y especificaciones DIN / ASTM para compras mayoristas y contratistas.
        </p>

        <div class="mt-5 flex flex-col lg:flex-row gap-3 lg:items-center justify-between">
            <div class="flex gap-2 overflow-x-auto pb-1">
                <a href="{{ route('catalog.index', request()->except('categoria', 'page')) }}"
                   class="whitespace-nowrap px-3 py-1.5 rounded text-label-md {{ request('categoria') ? 'bg-line-soft text-on-surface hover:bg-surface-container-high' : 'bg-primary-container text-white' }}">
                    Todos los materiales
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('catalog.index', array_merge(request()->except('page'), ['categoria' => $category->slug])) }}"
                       class="whitespace-nowrap px-3 py-1.5 rounded text-label-md {{ request('categoria') === $category->slug ? 'bg-primary-container text-white' : 'bg-line-soft text-on-surface hover:bg-surface-container-high' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <form method="GET" class="flex gap-2">
                @if (request('categoria'))
                    <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                @endif
                <div class="relative flex-1 lg:w-72">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[20px] text-muted">search</span>
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar por SKU, norma o término..." class="input pl-9">
                </div>
                <select name="orden" class="input w-auto pr-8" onchange="this.form.submit()">
                    <option value="">Disponibilidad</option>
                    <option value="precio_asc" @selected(request('orden') === 'precio_asc')>Precio: menor a mayor</option>
                    <option value="precio_desc" @selected(request('orden') === 'precio_desc')>Precio: mayor a menor</option>
                    <option value="nombre" @selected(request('orden') === 'nombre')>Nombre</option>
                </select>
            </form>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6">
        @if ($products->isEmpty())
            <div class="card p-10 text-center">
                <span class="material-symbols-outlined text-[48px] text-outline">search_off</span>
                <p class="mt-2 text-headline-sm text-primary">No encontramos productos</p>
                <p class="text-body-md text-muted">Prueba con otro término o categoría.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </div>
</x-app-layout>
