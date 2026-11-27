@extends('plantillas.principal')

@section('title', 'Añadir correo electrónico')
@section('topbar', 'Centros')

@section('content')

<div class="page-heading">
    <div>
        <h1>Añadir correo electrónico</h1>
        <p>Asociar un nuevo correo electrónico al centro {{ $centro->nombre }}.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ route('contactos.centros.correos.store', ['centroId' => $centro->id]) }}">

            @csrf

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
                           value="{{ old('email') }}"
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
                           value="{{ old('tipo') }}"
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
                        Correo principal
                    </label>
                </div>

                <div class="form-group">
                    <input type="hidden" name="activo" value="0">
                    <label class="checkbox-row">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               @checked(old('activo', true))>
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
                    Guardar correo
                </button>
            </div>

        </form>
    </div>
</div>

@endsection