@extends('layouts.app')

@section('title', 'Bitácora de Auditoría - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Bitácora de Auditoría del Sistema</h2>
    <p>Eventos registrados: {{ $audits->total() }}</p>
</div>
@endsection