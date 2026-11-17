{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

{{-- Título de la pestaña y de la barra superior. --}}
@section('title', 'Inicio')
@section('topbar', 'Inicio')

{{-- Contenido de esta pantalla: --}}
@section('content')
{{-- Saludo con el nombre del usuario. --}}
<div class="page-heading">
    <div>
        <h1>Hola, {{ $usuario->nombre }}</h1>
        <p>Bienvenido al panel principal.</p>
    </div>
</div>

{{-- Tarjeta de bienvenida. --}}
<div class="card welcome-card">
    <div class="card-body">
        <h2 style="margin:0 0 8px;">Panel principal</h2>
        <p style="margin:0; color:#64748b; line-height:1.65;">
            Esta es la base inicial de la aplicación. Los módulos de gestión se irán incorporando progresivamente.
        </p>
    </div>
</div>
@endsection
