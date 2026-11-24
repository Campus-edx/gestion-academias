{{-- Formulario de materia: copia del de categorías cambiando los nombres. --}}
@extends('plantillas.principal')

@section('title', $materia->exists ? 'Editar materia' : 'Nueva materia')
@section('topbar', 'Materias')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $materia->exists ? 'Editar materia' : 'Nueva materia' }}</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $materia->exists ? route('materias.update', $materia) : route('materias.store') }}">
            @csrf
            @if($materia->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" type="text" value="{{ old('nombre', $materia->nombre) }}">
                    @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $materia->activo))>
                        Activa
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('materias.index') }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
