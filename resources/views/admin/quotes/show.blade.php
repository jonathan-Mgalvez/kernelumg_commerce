<x-admin-layout :title="'Cotización '.$quote->code">
    <x-slot name="actions">
        <a href="{{ route('quote.pdf', $quote) }}" class="btn-secondary"><span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> PDF</a>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            @include('quotes._summary', ['quote' => $quote, 'editable' => false])
        </div>
        <aside class="space-y-4">
            <form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="card p-4 space-y-3">
                @csrf @method('PATCH')
                <label class="label" for="status">Estado</label>
                <select id="status" name="status" class="input">
                    @foreach (\App\Models\Quote::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($quote->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn-primary w-full">Actualizar estado</button>
            </form>

            <div class="card p-4 space-y-2 text-body-md">
                <p class="overline">Cliente</p>
                <p class="font-semibold">{{ $quote->user->name }}</p>
                <p>{{ $quote->user->company }}</p>
                <p class="text-muted">{{ $quote->user->email }} · {{ $quote->user->phone }}</p>
                <p class="overline pt-2">Despacho</p>
                <p>{{ $quote->delivery_address ?? '—' }}</p>
                @if ($quote->notes)<p class="p-3 bg-surface-container-low rounded">{{ $quote->notes }}</p>@endif
                <p class="text-muted pt-2">Creada {{ $quote->created_at->format('d/m/Y H:i') }} · vence {{ $quote->valid_until?->format('d/m/Y') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}" onsubmit="return confirm('¿Eliminar la cotización {{ $quote->code }}?')">
                @csrf @method('DELETE')
                <button class="btn-danger w-full">Eliminar cotización</button>
            </form>
        </aside>
    </div>
</x-admin-layout>
