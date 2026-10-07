@extends('layouts.app')

@section('title', 'Compra Exitosa - KernelUMG Commerce')

@section('content')
<div style="padding: 20px;">
    <h2>¡Pedido Confirmado!</h2>
    <p>Código de seguimiento: <strong>{{ $order->tracking_code }}</strong></p>
</div>
@endsection