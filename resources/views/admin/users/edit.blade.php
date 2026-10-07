@extends('layouts.app')

@section('title', 'Editar Usuario - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Editar Usuario: {{ $user->name }}</h2>
    <p>Edición de permisos y perfil.</p>
</div>
@endsection