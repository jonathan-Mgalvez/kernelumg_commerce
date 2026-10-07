@extends('layouts.app')

@section('title', 'Dashboard - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h1>Dashboard Administrativo</h1>
    <p>Resumen Ejecutivo y Métricas del Sistema (Vista Base)</p>
    <ul>
        <li>Ventas Brutas: Q {{ number_format($metrics['total_sales'] ?? 0, 2) }}</li>
        <li>Pedidos Pendientes: {{ $metrics['pending_orders'] ?? 0 }}</li>
        <li>Productos Agotados: {{ $metrics['out_of_stock_count'] ?? 0 }}</li>
        <li>Mensajes No Leídos: {{ $metrics['unread_messages'] ?? 0 }}</li>
        <li>Tasa Conversión Cotizaciones: {{ $metrics['conversion_rate'] ?? 0 }}%</li>
    </ul>
</div>
@endsection