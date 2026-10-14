<x-admin-layout title="Lotes y Almacén">
    <x-slot name="actions">
        <span class="chip">Valor del inventario: Q{{ number_format($valor, 2) }}</span>
    </x-slot>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar SKU o producto..." class="input w-64">
        <select name="estado" class="input w-auto pr-8" onchange="this.form.submit()">
            <option value="">Todo el inventario</option>
            <option value="bajo" @selected(request('estado') === 'bajo')>Solo stock bajo / agotado</option>
        </select>
        <button class="btn-secondary">Filtrar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[760px]">
            <thead>
                <tr><th>SKU</th><th>Producto</th><th>Ubicación</th><th class="!text-right">Stock</th><th class="!text-right">Mínimo</th><th>Estado</th><th class="!text-right">Valor</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($products as $p)
                    <tr>
                        <td class="text-muted">{{ $p->sku }}</td>
                        <td><p class="font-semibold">{{ $p->name }}</p><p class="text-body-sm text-muted">{{ $p->category->name }}</p></td>
                        <td>{{ $p->location ?? '—' }}</td>
                        <td class="num font-semibold">{{ number_format($p->stock) }}</td>
                        <td class="num">{{ number_format($p->min_stock) }}</td>
                        <td>
                            @if ($p->stock_status === 'agotado')<span class="chip-danger">Agotado</span>
                            @elseif ($p->stock_status === 'bajo')<span class="chip-warn">Stock bajo</span>
                            @else<span class="chip-ok">En stock</span>@endif
                        </td>
                        <td class="num">Q{{ number_format($p->stock * $p->price, 2) }}</td>
                        <td class="text-right"><a href="{{ route('admin.products.edit', $p) }}" class="btn-secondary btn-sm">Ajustar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</x-admin-layout>
