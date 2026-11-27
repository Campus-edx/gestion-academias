@extends('plantillas.principal')

@section('title', 'Editar correo electrónico')
@section('topbar', 'Centros')

@section('content')

<div class="page-heading">
    <div>
        <h1>Editar correo electrónico</h1>
        <p>Modificar el correo electrónico asociado al centro {{ $centro->nombre }}.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ route('contactos.centros.correos.update', ['centroId' => $centro->id, 'relacionId' => $relacion->id]) }}">

            @csrf
            @method('PUT')

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="email">
                        Correo electrónico
                    </label>

                    <input class="form-control"
                           id="email"
                           name="email"
                           type="email"
                           maxlength="255"
                           value="{{ old('email', $relacion->correoElectronico?->email) }}"
                           required>

                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="tipo">
                        Tipo de correo
                    </label>

                    <input class="form-control"
                           id="tipo"
                           name="tipo"
                           type="text"
                           maxlength="40"
                           value="{{ old('tipo', $relacion->tipo) }}"
                           required>

                    @error('tipo')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="observaciones">
                        Observaciones del correo
                    </label>

                    <textarea class="form-control"
                              id="observaciones"
                              name="observaciones"
                              rows="3">{{ old('observaciones', $relacion->correoElectronico?->observaciones) }}</textarea>

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
                              rows="3">{{ old('observaciones_relacion', $relacion->observaciones) }}</textarea>

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
                               @checked(old('principal', $relacion->principal))>
                        Correo principal
                    </label>
                </div>

                <div class="form-group">
                    <input type="hidden" name="activo" value="0">
                    <label class="checkbox-row">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               @checked(old('activo', $relacion->activo))>
                        Correo activo
                    </label>
                </div>

            </div>

            <div class="form-actions">
                <a class="btn btn-secondary"
                   href="{{ route('contactos.centros.show', ['id' => $centro->id]) }}">
                    Cancelar
                </a>

                <button class="btn btn-primary" type="submit">
                    Guardar cambios
                </button>
            </div>

        </form>
    </div>
</div>

@endsection