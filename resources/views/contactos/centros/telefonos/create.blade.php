@extends('plantillas.principal')

@section('title', 'Añadir teléfono')
@section('topbar', 'Centros')

@section('content')

<div class="page-heading">
    <div>
        <h1>Añadir teléfono</h1>
        <p>Asociar un nuevo teléfono al centro {{ $centro->nombre }}.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ route('contactos.centros.telefonos.store', ['centroId' => $centro->id]) }}">

            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="prefijo_internacional">
                        Prefijo internacional
                    </label>

                    <input class="form-control"
                           id="prefijo_internacional"
                           name="prefijo_internacional"
                           type="text"
                           maxlength="8"
                           placeholder="+34"
                           value="{{ old('prefijo_internacional') }}">

                    @error('prefijo_internacional')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="numero">
                        Número de teléfono
                    </label>

                    <input class="form-control"
                           id="numero"
                           name="numero"
                           type="text"
                           maxlength="30"
                           value="{{ old('numero') }}"
                           required>

                    @error('numero')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="extension">
                        Extensión
                    </label>

                    <input class="form-control"
                           id="extension"
                           name="extension"
                           type="text"
                           maxlength="10"
                           value="{{ old('extension') }}">

                    @error('extension')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="tipo">
                        Tipo de teléfono
                    </label>

                    <input class="form-control"
                           id="tipo"
                           name="tipo"
                           type="text"
                           maxlength="40"
                           value="{{ old('tipo') }}"
                           required>

                    @error('tipo')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="observaciones">
                        Observaciones del teléfono
                    </label>

                    <textarea class="form-control"
                              id="observaciones"
                              name="observaciones"
                              rows="3">{{ old('observaciones') }}</textarea>

                    @error('observaciones')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="observaciones_relacion">
                        Observaciones de la asociación con el centro
                    </label>

                    <textarea class="form-control"
                              id="observaciones_relacion"
                              name="observaciones_relacion"
                              rows="3">{{ old('observaciones_relacion') }}</textarea>

                    @error('observaciones_relacion')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <input type="hidden" name="principal" value="0">
                    <label class="checkbox-row">
                        <input type="checkbox"
                               name="principal"
                               value="1"
                               @checked(old('principal', false))>
                        Teléfono principal
                    </label>
                </div>

                <div class="form-group">
                    <input type="hidden" name="activo" value="0">
                    <label class="checkbox-row">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               @checked(old('activo', true))>
                        Teléfono activo
                    </label>
                </div>

            </div>

            <div class="form-actions">
                <a class="btn btn-secondary"
                   href="{{ route('contactos.centros.show', ['id' => $centro->id]) }}">
                    Cancelar
                </a>

                <button class="btn btn-primary" type="submit">
                    Guardar teléfono
                </button>
            </div>

        </form>
    </div>
</div>

@endsection