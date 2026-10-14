<x-admin-layout :title="$category->exists ? 'Editar categoría' : 'Nueva categoría'">
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="card p-5 grid gap-4 max-w-xl">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <div>
            <label class="label" for="name">Nombre *</label>
            <input class="input" id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="100">
        </div>
        <div>
            <label class="label" for="icon">Ícono (nombre de Material Symbols) *</label>
            <input class="input" id="icon" name="icon" value="{{ old('icon', $category->icon) }}" required maxlength="50">
            <p class="mt-1 text-body-sm text-muted">Ejemplos: construction, hardware, foundation, plumbing, electrical_services. Ver fonts.google.com/icons</p>
        </div>
        <div>
            <label class="label" for="description">Descripción</label>
            <textarea class="input h-24 py-2" id="description" name="description" maxlength="500">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="flex gap-2">
            <button class="btn-primary">Guardar</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-admin-layout>
