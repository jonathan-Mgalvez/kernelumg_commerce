<x-admin-layout title="Cotizaciones">
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input name="q" value="{{ request('q') }}" placeholder="Código COT o cliente..." class="input w-64">
        <select name="estado" class="input w-auto pr-8" onchange="this.form.submit()">
            <option value="">Todos los estados</option>
            @foreach (\App\Models\Quote::STATUSES as $key => $label)
                @continue($key === 'borrador')
                <option value="{{ $key }}" @selected(request('estado') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn-secondary">Filtrar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[760px]">
            <thead><tr><th>Código</th><th>Cliente / Empresa</th><th>Fecha</th><th class="!text-right">Partidas</th><th class="!text-right">Total</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($quotes as $q)
                    <tr>
                        <td class="font-semibold text-primary">{{ $q->code }}</td>
                        <td>{{ $q->user->name }}<p class="text-body-sm text-muted">{{ $q->user->company }}</p></td>
                        <td>{{ $q->created_at->format('d/m/Y') }}</td>
                        <td class="num">{{ $q->items_count }}</td>
                        <td class="num font-semibold">Q{{ number_format($q->total, 2) }}</td>
                        <td><span class="{{ $q->status_chip }}">{{ $q->status_label }}</span></td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.quotes.show', $q) }}" class="btn-secondary btn-sm">Ver</a>
                            <a href="{{ route('quote.pdf', $q) }}" class="btn-secondary btn-sm"><span class="material-symbols-outlined text-[16px]">picture_as_pdf</span></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-8">No hay cotizaciones.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $quotes->links() }}</div>
</x-admin-layout>
