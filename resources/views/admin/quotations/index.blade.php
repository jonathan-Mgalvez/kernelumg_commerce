@extends('layouts.app')

@section('title', 'Cotizaciones - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Auditoría de Cotizaciones (Vista Base)</h2>
    <p>Cotizaciones registradas: {{ $quotations->total() }}</p>
</div>
@endsection