@extends('layouts.app')

@section('title', 'Detalle de Cotización - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Cotización #{{ $quotation->id }}</h2>
    <p>Total estimado: Q {{ number_format($quotation->total, 2) }}</p>
</div>
@endsection