<x-app-layout>
    <x-slot:title>Historial</x-slot:title>
    <x-slot name="header">
        <p class="overline">Mi cuenta</p>
        <h1 class="text-headline-lg text-primary">Historial de Cotizaciones</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 space-y-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="estado" class="input w-auto pr-8" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                @foreach (\App\Models\Quote::STATUSES as $key => $label)
                    @continue($key === 'borrador')
                    <option value="{{ $key }}" @selected(request('estado') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ route('quote.index') }}" class="btn-primary ml-auto">
                <span class="material-symbols-outlined text-[18px]">add</span> Nueva cotización
            </a>
        </form>

        <div class="card overflow-x-auto">
            <table class="table-ft min-w-[640px]">
                <thead>
                    <tr><th>Código</th><th>Fecha</th><th class="!text-right">Partidas</th><th class="!text-right">Total</th><th>Estado</th><th>Vence</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($quotes as $quote)
                        <tr>
                            <td class="font-semibold text-primary">{{ $quote->code }}</td>
                            <td>{{ $quote->created_at->format('d/m/Y') }}</td>
                            <td class="num">{{ $quote->items_count }}</td>
                            <td class="num font-semibold">Q{{ number_format($quote->total, 2) }}</td>
                            <td><span class="{{ $quote->status_chip }}">{{ $quote->status_label }}</span></td>
                            <td>{{ $quote->valid_until?->format('d/m/Y') }}</td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('quote.show', $quote) }}" class="btn-secondary btn-sm">Ver</a>
                                <a href="{{ route('quote.pdf', $quote) }}" class="btn-secondary btn-sm" title="Descargar PDF">
                                    <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-muted">Todavía no has enviado cotizaciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $quotes->links() }}
    </div>
</x-app-layout>
