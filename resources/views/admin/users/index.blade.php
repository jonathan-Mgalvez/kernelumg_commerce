@extends('layouts.app')

@section('title', 'Control de Usuarios - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Gestión Integral de Usuarios</h2>
    <p>Cuentas registradas: {{ $users->total() }}</p>
</div>
@endsection