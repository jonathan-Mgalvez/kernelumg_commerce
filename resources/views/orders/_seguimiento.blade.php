{{-- Línea de tiempo del estado del pedido. Requiere $order. --}}
@php
    $pasos = ['pendiente' => 'receipt_long', 'procesando' => 'inventory', 'despachado' => 'local_shipping', 'entregado' => 'where_to_vote'];
    $actual = array_search($order->status, array_keys($pasos), true);
@endphp

@if ($order->status === 'cancelado')
    <div class="card p-4 flex items-center gap-3 border-danger/40 bg-danger/5">
        <span class="material-symbols-outlined text-danger text-[32px]">cancel</span>
        <div>
            <p class="text-label-lg text-danger">Pedido cancelado</p>
            <p class="text-body-sm text-muted">El stock reservado fue liberado.</p>
        </div>
    </div>
@else
    <div class="card p-4">
        <p class="overline mb-4">Seguimiento</p>
        <ol class="grid grid-cols-4">
            @foreach ($pasos as $estado => $icono)
                @php($hecho = $loop->index <= $actual)
                <li class="relative flex flex-col items-center text-center">
                    @unless ($loop->first)
                        <span class="absolute top-5 right-1/2 w-full h-0.5 {{ $hecho ? 'bg-primary-container' : 'bg-line' }}"></span>
                    @endunless
                    <span class="relative z-10 w-10 h-10 rounded-full flex items-center justify-center {{ $hecho ? 'bg-primary-container text-white' : 'bg-surface-container text-outline' }} {{ $loop->index === $actual ? 'ring-4 ring-primary-fixed' : '' }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $icono }}</span>
                    </span>
                    <span class="mt-2 text-label-md {{ $hecho ? 'text-primary' : 'text-muted' }}">{{ \App\Models\Order::STATUSES[$estado] }}</span>
                </li>
            @endforeach
        </ol>
    </div>
@endif
