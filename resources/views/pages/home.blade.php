@extends('layouts.app')

@section('title', 'Inicio - KernelUMG Commerce')

@section('content')
<div style="padding: 20px;">
    <h1>Soluciones Tecnológicas Integrales</h1>
    <p>Productos destacados cargados: {{ $featuredProducts->count() }}</p>
</div>
@endsection