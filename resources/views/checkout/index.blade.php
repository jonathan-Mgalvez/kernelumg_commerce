@extends('layouts.app')

@section('title', 'Finalizar Compra - KernelUMG Commerce')

@section('content')
<div style="padding: 20px;">
    <h2>Pasarela de Pago (Vista Base)</h2>
    <p>Total a pagar: Q {{ number_format($cartSummary['total'], 2) }}</p>
</div>
@endsection