<x-admin-layout title="Ofertas">
    <x-slot name="actions">
        <a href="{{ route('admin.offers.create') }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">add</span> Nueva oferta</a>
    </x-slot>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <select name="estado" class="input w-auto pr-8" onchange="this.form.submit()">
            <option value="">Todas las ofertas</option>
            <option value="vigente" @selected(request('estado') === 'vigente')>Solo vigentes</option>
        </select>
        <a href="{{ route('offers.index') }}" target="_blank" class="btn-secondary ml-auto"><span class="material-symbols-outlined text-[18px]">open_in_new</span> Ver página pública</a>
    </form>

    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[760px]">
            <thead><tr><th>Producto</th><th>Título</th><th class="!text-right">Descuento</th><th class="!text-right">Precio oferta</th><th>Vigencia</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($offers as $o)
                    <tr>
                        <td><p class="font-semibold">{{ $o->product->name }}</p><p class="text-body-sm text-muted">{{ $o->product->sku }}</p></td>
                        <td>{{ $o->title ?? '—' }}</td>
                        <td class="num font-semibold text-danger">−{{ rtrim(rtrim($o->discount_rate, '0'), '.') }}%</td>
                        <td class="num">
                            <span class="block text-body-sm text-muted line-through">Q{{ number_format($o->product->price, 2) }}</span>
                            Q{{ number_format($o->product->price * (100 - $o->discount_rate) / 100, 2) }}
                        </td>
                        <td class="text-body-sm">{{ $o->starts_at->format('d/m/Y') }} → {{ $o->ends_at->format('d/m/Y') }}</td>
                        <td><span class="{{ $o->estado_chip }}">{{ ucfirst($o->estado) }}</span></td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.offers.edit', $o) }}" class="btn-secondary btn-sm" title="Editar"><span class="material-symbols-outlined text-[16px]">edit</span></a>
                            <form method="POST" action="{{ route('admin.offers.destroy', $o) }}" class="inline" onsubmit="return confirm('¿Eliminar la oferta de {{ $o->product->sku }}?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm" title="Eliminar"><span class="material-symbols-outlined text-[16px]">delete</span></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-8">No hay ofertas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $offers->links() }}</div>
</x-admin-layout>
