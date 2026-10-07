@extends('layouts.app')

@section('title', 'Gestión de Pedidos - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Bandeja de Pedidos (Vista Base)</h2>
    <p>Total de órdenes listadas: {{ $orders->total() }}</p>
</div>
@endsection