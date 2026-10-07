@extends('layouts.app')

@section('title', 'Bandeja de Mensajes - Panel Administrativo')

@section('content')
<div style="padding: 20px;">
    <h2>Bandeja de Mensajes (Vista Base)</h2>
    <p>Mensajes listados: {{ $messages->total() }}</p>
</div>
@endsection