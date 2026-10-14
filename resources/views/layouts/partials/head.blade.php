<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="{{ config('empresa.eslogan') }}">

<title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name', 'FerroTek') }}</title>
<link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">

<!-- Fuentes: Inter + íconos Material Symbols (igual que el diseño de Stitch) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
