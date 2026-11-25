{{-- Página que enseña Laravel cuando alguien entra donde no tiene permiso (error 403).
     Laravel la busca sola por su nombre: resources/views/errors/403.blade.php.
     Sin este archivo saldría una página en inglés ("This action is unauthorized"). --}}
@extends('plantillas.principal')

@section('title', 'Sin permiso')
@section('topbar', 'Sin permiso')

@section('content')
<div class="card">
    <div class="card-body">
        <h1>No tienes permiso para entrar aquí</h1>
        <p>Tus roles no incluyen esta acción. Si crees que deberías poder, pídeselo al administrador.</p>
        <a class="btn btn-secondary" href="{{ route('dashboard') }}">Volver al inicio</a>
    </div>
</div>
@endsection