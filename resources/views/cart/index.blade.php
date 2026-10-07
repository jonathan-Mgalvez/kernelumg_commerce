@extends('layouts.app')

@section('title', 'Carrito de Compras - KernelUMG Commerce')

@section('content')
<div style="padding: 20px;">
    <h2>Cesta de Compras (Vista Base)</h2>
    <p>Total de artículos: {{ $cartSummary['total_items'] }}</p>
    <p>Subtotal: Q {{ number_format($cartSummary['subtotal'], 2) }}</p>
    <p>Total con IVA: Q {{ number_format($cartSummary['total'], 2) }}</p>
</div>
@endsection