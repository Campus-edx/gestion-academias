@extends('plantillas.principal')

@section('title', $centro->exists ? 'Editar centro' : 'Nuevo centro')
@section('topbar', 'Centros')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $centro->exists ? 'Editar centro' : 'Nuevo centro' }}</h1>
        <p>Datos generales del centro y empresa a la que pertenece.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ $centro->exists
                    ? route('contactos.centros.update', $centro->id)
                    : route('contactos.centros.store') }}">

            @csrf

            @if($centro->exists)
                @method('PUT')
            @endif

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="empresa_id">Empresa</label>

                    <select class="form-control" id="empresa_id" name="empresa_id" required>
                        <option value="">Selecciona una empresa</option>

                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}"
                                @selected(old('empresa_id', $centro->empresa_id) == $empresa->id)>
                                {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
                            </option>
                        @endforeach
                    </select>

                    @error('empresa_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre del centro</label>

                    <input class="form-control"
                           id="nombre"
                           name="nombre"
                           type="text"
                           maxlength="255"
                           value="{{ old('nombre', $centro->nombre) }}"
                           required>

                    @error('nombre')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               @checked(old('activo', $centro->activo))>
                        Centro activo
                    </label>
                </div>

            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('contactos.centros.index') }}">
                    Cancelar
                </a>

                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>

        </form>
    </div>
</div>

@endsection
