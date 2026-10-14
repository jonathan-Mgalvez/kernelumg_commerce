<x-admin-layout title="Categorías">
    <x-slot name="actions">
        <a href="{{ route('admin.categories.create') }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">add</span> Nueva categoría</a>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[560px]">
            <thead><tr><th></th><th>Nombre</th><th>Descripción</th><th class="!text-right">Productos</th><th></th></tr></thead>
            <tbody>
                @foreach ($categories as $c)
                    <tr>
                        <td class="w-10"><span class="material-symbols-outlined text-primary-container">{{ $c->icon }}</span></td>
                        <td class="font-semibold">{{ $c->name }}</td>
                        <td class="text-on-surface-variant">{{ $c->description }}</td>
                        <td class="num">{{ $c->products_count }}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.categories.edit', $c) }}" class="btn-secondary btn-sm"><span class="material-symbols-outlined text-[16px]">edit</span></a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="inline" onsubmit="return confirm('¿Eliminar la categoría {{ $c->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm"><span class="material-symbols-outlined text-[16px]">delete</span></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
