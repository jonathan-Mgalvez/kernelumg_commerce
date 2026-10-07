@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div style="padding: 20px;">
    <h2>Perfil de Usuario: {{ $user->name }}</h2>
</div>
@endsection