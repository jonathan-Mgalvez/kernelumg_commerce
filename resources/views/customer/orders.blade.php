@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('content')
<div style="padding: 20px;">
    <h2>Historial de Pedidos</h2>
    <p>Total de compras: {{ $orders->total() }}</p>
</div>
@endsection