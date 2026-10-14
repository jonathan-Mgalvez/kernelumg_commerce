<x-admin-layout title="Pedidos">
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input name="q" value="{{ request('q') }}" placeholder="Código PED o cliente..." class="input w-64">
        <select name="estado" class="input w-auto pr-8" onchange="this.form.submit()">
            <option value="">Todos los estados</option>
            @foreach (\App\Models\Order::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('estado') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn-secondary">Filtrar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[820px]">
            <thead><tr><th>Pedido</th><th>Cliente / Empresa</th><th>Fecha</th><th class="!text-right">Productos</th><th class="!text-right">Total</th><th>Pago</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr>
                        <td class="font-semibold text-primary">{{ $o->code }}@if($o->quote_id)<span class="chip ml-1" title="Desde cotización">COT</span>@endif</td>
                        <td>{{ $o->user->name }}<p class="text-body-sm text-muted">{{ $o->user->company }}</p></td>
                        <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        <td class="num">{{ $o->items_count }}</td>
                        <td class="num font-semibold">Q{{ number_format($o->total, 2) }}</td>
                        <td class="text-body-sm">{{ $o->payment_label }}</td>
                        <td><span class="{{ $o->status_chip }}">{{ $o->status_label }}</span></td>
                        <td class="text-right"><a href="{{ route('admin.orders.show', $o) }}" class="btn-secondary btn-sm">Gestionar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-8">No hay pedidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin-layout>
