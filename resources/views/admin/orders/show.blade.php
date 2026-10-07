@extends('layouts.app')

@section('title', 'Detalle de Pedido - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Detalle de Orden #{{ $order->tracking_code }}</h2>
    <p>Estado actual: <strong>{{ $order->status }}</strong></p>
</div>
@endsection