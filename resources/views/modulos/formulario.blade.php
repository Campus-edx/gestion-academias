{{-- Formulario de módulo: nombre, materia (obligatoria), nivel (opcional), descripción y activo. --}}
@extends('plantillas.principal')

@section('title', $modulo->exists ? 'Editar módulo' : 'Nuevo módulo')
@section('topbar', 'Módulos')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $modulo->exists ? 'Editar módulo' : 'Nuevo módulo' }}</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $modulo->exists ? route('modulos.update', $modulo) : route('modulos.store') }}">
            @csrf
            @if($modulo->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" type="text" value="{{ old('nombre', $modulo->nombre) }}">
                    @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Desplegable de materias. La primera opción, vacía, obliga a elegir una. --}}
                <div class="form-group">
                    <label class="form-label" for="materia_id">Materia</label>
                    <select class="form-select" id="materia_id" name="materia_id">
                        <option value="">Elige…</option>
                        @foreach($materias as $materia)
                            <option value="{{ $materia->id }}" @selected((string) old('materia_id', $modulo->materia_id) === (string) $materia->id)>{{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                    @error('materia_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Nivel: opcional ("Sin nivel"). --}}
                <div class="form-group">
                    <label class="form-label" for="nivel_id">Nivel</label>
                    <select class="form-select" id="nivel_id" name="nivel_id">
                        <option value="">Sin nivel</option>
                        @foreach($niveles as $nivel)
                            <option value="{{ $nivel->id }}" @selected((string) old('nivel_id', $modulo->nivel_id) === (string) $nivel->id)>{{ $nivel->nombre }}</option>
                        @endforeach
                    </select>
                    @error('nivel_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group full">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $modulo->descripcion) }}</textarea>
                    @error('descripcion')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $modulo->activo))>
                        Activo
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('modulos.index') }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
