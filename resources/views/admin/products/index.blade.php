<x-admin-layout title="Productos">
    <x-slot name="actions">
        <a href="{{ route('admin.products.create') }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">add</span> Nuevo producto</a>
    </x-slot>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar SKU o nombre..." class="input w-64">
        <select name="categoria" class="input w-auto pr-8" onchange="this.form.submit()">
            <option value="">Todas las categorías</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected(request('categoria') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <button class="btn-secondary">Filtrar</button>
    </form>

    {{-- Acciones masivas: las casillas de la tabla pertenecen a este formulario (atributo form="masivo") --}}
    <form id="masivo" method="POST" action="{{ route('admin.products.bulk') }}"
          x-data="{ n: 0, accion: '' }" @change.window="n = document.querySelectorAll('input[form=masivo][name=\'ids[]\']:checked').length"
          class="card mb-3 p-3 flex flex-wrap items-center gap-2" :class="n ? 'border-primary-container' : ''"
          onsubmit="return this.accion.value !== 'eliminar' || confirm('¿Eliminar los productos seleccionados? Los que tengan historial se desactivarán.')">
        @csrf @method('PATCH')
        <span class="text-label-lg" :class="n ? 'text-primary' : 'text-muted'"><span x-text="n">0</span> seleccionado(s)</span>
        <select name="accion" x-model="accion" class="input w-auto pr-8" required>
            <option value="">Acción masiva...</option>
            <option value="activar">Activar</option>
            <option value="desactivar">Desactivar</option>
            <option value="precio">Ajustar precio (%)</option>
            <option value="stock">Sumar/restar stock</option>
            <option value="eliminar">Eliminar</option>
        </select>
        <input type="number" name="valor" step="any" class="input w-36" x-show="accion === 'precio' || accion === 'stock'" x-cloak
               :placeholder="accion === 'precio' ? 'Ej. 5 o -10' : 'Ej. 100 o -20'" :required="accion === 'precio' || accion === 'stock'">
        <button class="btn-primary" :disabled="!n || !accion">Aplicar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[760px]">
            <thead>
                <tr>
                    <th class="w-8"><input type="checkbox" class="rounded-sm border-outline-variant text-primary-container" aria-label="Seleccionar todos"
                        onchange="document.querySelectorAll('input[form=masivo][name=\'ids[]\']').forEach(c => c.checked = this.checked)"></th>
                    <th></th><th>SKU</th><th>Producto</th><th>Categoría</th><th class="!text-right">Precio</th><th class="!text-right">Stock</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr>
                        <td><input type="checkbox" form="masivo" name="ids[]" value="{{ $p->id }}" class="rounded-sm border-outline-variant text-primary-container" aria-label="Seleccionar {{ $p->sku }}"></td>
                        <td class="w-14">
                            <div class="w-10 h-10 rounded bg-surface-container overflow-hidden flex items-center justify-center">
                                @if ($p->image_url)<img src="{{ $p->image_url }}" alt="" class="w-full h-full object-cover">
                                @else<span class="material-symbols-outlined text-outline text-[20px]">{{ $p->category->icon }}</span>@endif
                            </div>
                        </td>
                        <td class="text-muted">{{ $p->sku }}</td>
                        <td class="font-semibold">{{ $p->name }} @if($p->featured)<span class="chip-info ml-1">Destacado</span>@endif</td>
                        <td>{{ $p->category->name }}</td>
                        <td class="num">Q{{ number_format($p->price, 2) }}</td>
                        <td class="num {{ $p->stock_status !== 'ok' ? 'text-warn font-semibold' : '' }}">{{ number_format($p->stock) }}</td>
                        <td>{!! $p->active ? '<span class="chip-ok">Activo</span>' : '<span class="chip">Inactivo</span>' !!}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.offers.create', ['producto' => $p->id]) }}" class="btn-secondary btn-sm" title="Crear oferta"><span class="material-symbols-outlined text-[16px]">sell</span></a>
                            <a href="{{ route('admin.products.edit', $p) }}" class="btn-secondary btn-sm" title="Editar"><span class="material-symbols-outlined text-[16px]">edit</span></a>
                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="inline" onsubmit="return confirm('¿Eliminar {{ $p->sku }}?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm" title="Eliminar"><span class="material-symbols-outlined text-[16px]">delete</span></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-8">No hay productos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</x-admin-layout>
