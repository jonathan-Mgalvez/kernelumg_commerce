@extends('layouts.app')

@section('title', 'Rastreo de Pedido')

@section('content')
<div style="padding: 20px;">
    <h2>Seguimiento de Pedido: #{{ $order->tracking_code }}</h2>
    <p>Estado actual: {{ $order->status }}</p>
</div>
@endsection