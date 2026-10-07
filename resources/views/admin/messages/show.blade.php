@extends('layouts.app')

@section('title', 'Consulta de Contacto - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Mensaje de: {{ $message->name }}</h2>
    <p>Asunto: {{ $message->subject }}</p>
    <p>Estado: {{ $message->status }}</p>
</div>
@endsection