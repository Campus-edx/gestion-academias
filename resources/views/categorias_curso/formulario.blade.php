{{-- Formulario de categoría: sirve para crear y para editar ($categoria->exists). --}}
@extends('plantillas.principal')

@section('title', $categoria->exists ? 'Editar categoría' : 'Nueva categoría')
@section('topbar', 'Categorías de curso')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $categoria->exists ? 'Editar categoría' : 'Nueva categoría' }}</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $categoria->exists ? route('categorias_curso.update', $categoria) : route('categorias_curso.store') }}">
            @csrf
            @if($categoria->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" type="text" value="{{ old('nombre', $categoria->nombre) }}">
                    @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Desactivarla = deja de salir en el desplegable de los cursos nuevos. --}}
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $categoria->activo))>
                        Activa
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('categorias_curso.index') }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection