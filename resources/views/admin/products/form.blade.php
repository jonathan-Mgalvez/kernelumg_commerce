<x-admin-layout :title="$product->exists ? 'Editar producto' : 'Nuevo producto'">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          class="card p-5 grid gap-4 md:grid-cols-2">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div>
            <label class="label" for="sku">SKU *</label>
            <input class="input" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required maxlength="30" placeholder="FT-XX-0000">
        </div>
        <div>
            <label class="label" for="category_id">Categoría *</label>
            <select class="input" id="category_id" name="category_id" required>
                <option value="">Selecciona...</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="label" for="name">Nombre *</label>
            <input class="input" id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255">
        </div>
        <div>
            <label class="label" for="subtitle">Subtítulo / características</label>
            <input class="input" id="subtitle" name="subtitle" value="{{ old('subtitle', $product->subtitle) }}" maxlength="255">
        </div>
        <div>
            <label class="label" for="standard">Norma / especificación</label>
            <input class="input" id="standard" name="standard" value="{{ old('standard', $product->standard) }}" maxlength="50" placeholder="ASTM A615">
        </div>
        <div class="md:col-span-2">
            <label class="label" for="description">Descripción</label>
            <textarea class="input h-24 py-2" id="description" name="description" maxlength="2000">{{ old('description', $product->description) }}</textarea>
        </div>
        <div>
            <label class="label" for="price">Precio (Q) *</label>
            <input class="input" type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $product->price) }}" required>
        </div>
        <div>
            <label class="label" for="unit">Unidad *</label>
            <input class="input" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required maxlength="20" list="units">
            <datalist id="units"><option value="unidad"><option value="caja"><option value="metro"><option value="quintal"><option value="galón"></datalist>
        </div>
        <div>
            <label class="label" for="stock">Stock actual *</label>
            <input class="input" type="number" min="0" id="stock" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required>
        </div>
        <div>
            <label class="label" for="min_stock">Stock mínimo *</label>
            <input class="input" type="number" min="0" id="min_stock" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? 0) }}" required>
        </div>
        <div>
            <label class="label" for="location">Ubicación en almacén</label>
            <input class="input" id="location" name="location" value="{{ old('location', $product->location) }}" maxlength="50" placeholder="Bahía C-14">
        </div>
        <div>
            <label class="label" for="image">Imagen (máx. 2 MB)</label>
            <input type="file" id="image" name="image" accept="image/*" class="block w-full text-body-md file:mr-3 file:h-9 file:px-3 file:border file:border-solid file:border-line file:rounded file:bg-white file:text-label-lg file:cursor-pointer">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="" class="mt-2 h-20 rounded border border-line">
            @endif
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-6">
            <label class="flex items-center gap-2 text-body-md">
                <input type="checkbox" name="active" value="1" class="rounded-sm border-outline-variant text-primary-container" @checked(old('active', $product->active))> Activo (visible en el catálogo)
            </label>
            <label class="flex items-center gap-2 text-body-md">
                <input type="checkbox" name="featured" value="1" class="rounded-sm border-outline-variant text-primary-container" @checked(old('featured', $product->featured))> Destacado en inicio
            </label>
        </div>
        <div class="md:col-span-2 flex gap-2 pt-2 border-t border-line-soft">
            <button class="btn-primary">{{ $product->exists ? 'Guardar cambios' : 'Crear producto' }}</button>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-admin-layout>
