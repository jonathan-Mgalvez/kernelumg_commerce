{{-- Campos de entrega y pago (checkout y "convertir cotización en pedido"). Opcional: $direccion. --}}
<div>
    <label class="label" for="shipping_address">Dirección de entrega *</label>
    <input class="input" id="shipping_address" name="shipping_address" required maxlength="255"
           value="{{ old('shipping_address', $direccion ?? '') }}" placeholder="Obra, bodega o dirección completa">
</div>
<fieldset>
    <legend class="label">Forma de pago *</legend>
    <div class="grid gap-2 sm:grid-cols-3">
        @foreach ([['transferencia', 'account_balance'], ['deposito', 'savings'], ['contra_entrega', 'payments']] as [$valor, $icono])
            <label class="flex items-center gap-2 p-3 rounded border border-line cursor-pointer hover:border-primary-container has-[:checked]:border-primary-container has-[:checked]:bg-primary-fixed/40">
                <input type="radio" name="payment_method" value="{{ $valor }}" class="text-primary-container" required @checked(old('payment_method', 'transferencia') === $valor)>
                <span class="material-symbols-outlined text-primary-container text-[20px]">{{ $icono }}</span>
                <span class="text-label-lg">{{ \App\Models\Order::PAGOS[$valor] }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
<div>
    <label class="label" for="notes">Indicaciones (opcional)</label>
    <textarea class="input h-20 py-2" id="notes" name="notes" maxlength="1000" placeholder="Horario de recepción, persona que recibe...">{{ old('notes') }}</textarea>
</div>
