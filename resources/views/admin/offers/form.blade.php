<x-admin-layout :title="$offer->exists ? 'Editar oferta' : 'Nueva oferta'">
    <form method="POST" action="{{ $offer->exists ? route('admin.offers.update', $offer) : route('admin.offers.store') }}"
          class="card p-5 grid gap-4 md:grid-cols-2 max-w-3xl"
          x-data="{ precios: @js($products->pluck('price', 'id')), producto: '{{ old('product_id', $offer->product_id) }}', pct: {{ (float) old('discount_rate', $offer->discount_rate ?? 10) }} }">
        @csrf
        @if ($offer->exists) @method('PUT') @endif

        <div class="md:col-span-2">
            <label class="label" for="product_id">Producto *</label>
            <select class="input" id="product_id" name="product_id" required x-model="producto">
                <option value="">Selecciona...</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected(old('product_id', $offer->product_id) == $p->id)>{{ $p->sku }} · {{ $p->name }} (Q{{ number_format($p->price, 2) }})</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="label" for="title">Título (opcional)</label>
            <input class="input" id="title" name="title" value="{{ old('title', $offer->title) }}" maxlength="100" placeholder="Liquidación de temporada">
        </div>
        <div>
            <label class="label" for="discount_rate">Descuento (%) *</label>
            <input class="input" type="number" step="0.5" min="1" max="90" id="discount_rate" name="discount_rate" x-model.number="pct" required>
        </div>
        <div class="flex items-end">
            <p class="w-full p-2 rounded bg-surface-container-low text-body-md" x-show="producto && precios[producto]">
                Precio con oferta:
                <span class="text-muted line-through" x-text="'Q' + Number(precios[producto]).toFixed(2)"></span>
                <strong class="text-danger" x-text="'Q' + (precios[producto] * (100 - pct) / 100).toFixed(2)"></strong>
            </p>
        </div>
        <div>
            <label class="label" for="starts_at">Inicia *</label>
            <input class="input" type="datetime-local" id="starts_at" name="starts_at" required value="{{ old('starts_at', $offer->starts_at?->format('Y-m-d\TH:i')) }}">
        </div>
        <div>
            <label class="label" for="ends_at">Termina *</label>
            <input class="input" type="datetime-local" id="ends_at" name="ends_at" required value="{{ old('ends_at', $offer->ends_at?->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-2 text-body-md">
                <input type="checkbox" name="active" value="1" class="rounded-sm border-outline-variant text-primary-container" @checked(old('active', $offer->active))> Activa
            </label>
        </div>
        <div class="md:col-span-2 flex gap-2 pt-2 border-t border-line-soft">
            <button class="btn-primary">{{ $offer->exists ? 'Guardar cambios' : 'Crear oferta' }}</button>
            <a href="{{ route('admin.offers.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-admin-layout>
