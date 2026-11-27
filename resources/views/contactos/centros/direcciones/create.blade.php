@extends('plantillas.principal')

@section('title', 'Añadir dirección')
@section('topbar', 'Centros')

@section('content')

<div class="page-heading">
    <div>
        <h1>Añadir dirección</h1>
        <p>Asociar una nueva dirección al centro {{ $centro->nombre }}.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ route('contactos.centros.direcciones.store', ['centroId' => $centro->id]) }}">

            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="linea_direccion_1">
                        Dirección
                    </label>

                    <input class="form-control"
                           id="linea_direccion_1"
                           name="linea_direccion_1"
                           type="text"
                           maxlength="220"
                           value="{{ old('linea_direccion_1') }}"
                           required>

                    @error('linea_direccion_1')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="linea_direccion_2">
                        Segunda línea de dirección
                    </label>

                    <input class="form-control"
                           id="linea_direccion_2"
                           name="linea_direccion_2"
                           type="text"
                           maxlength="220"
                           value="{{ old('linea_direccion_2') }}">

                    @error('linea_direccion_2')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="codigo_postal">
                        Código postal
                    </label>

                    <input class="form-control"
                           id="codigo_postal"
                           name="codigo_postal"
                           type="text"
                           maxlength="15"
                           value="{{ old('codigo_postal') }}">

                    @error('codigo_postal')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="municipio_id">
                        Municipio
                    </label>

                    <select class="form-control"
                            id="municipio_id"
                            name="municipio_id"
                            required>
                        <option value="">Selecciona un municipio</option>

                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio->id }}"
                                @selected(old('municipio_id') == $municipio->id)>
                                {{ $municipio->nombre }}
                                ({{ $municipio->provincia?->nombre ?? 'Sin provincia' }})
                            </option>
                        @endforeach
                    </select>

                    @error('municipio_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="tipo">
                        Tipo de dirección
                    </label>

                    <select class="form-control" id="tipo" name="tipo">
                        <option value="general" @selected(old('tipo', 'general') === 'general')>
                            General
                        </option>
                        <option value="fiscal" @selected(old('tipo') === 'fiscal')>
                            Fiscal
                        </option>
                        <option value="postal" @selected(old('tipo') === 'postal')>
                            Postal
                        </option>
                    </select>

                    @error('tipo')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="observaciones">
                        Observaciones de la dirección
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
                    <input type="hidden" name="requiere_revision" value="0">
                    <label class="checkbox-row">
                        <input type="checkbox"
                               name="requiere_revision"
                               value="1"
                               @checked(old('requiere_revision', false))>
                        Requiere revisión
                    </label>
                </div>

                <div class="form-group">
                    <input type="hidden" name="principal" value="0">
                    <label class="checkbox-row">
                        <input type="checkbox"
                               name="principal"
                               value="1"
                               @checked(old('principal', false))>
                        Dirección principal
                    </label>
                </div>

                <div class="form-group">
                    <input type="hidden" name="activo" value="0">
                    <label class="checkbox-row">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               @checked(old('activo', true))>
                        Dirección activa
                    </label>
                </div>

            </div>

            <div class="form-actions">
                <a class="btn btn-secondary"
                   href="{{ route('contactos.centros.show', ['id' => $centro->id]) }}">
                    Cancelar
                </a>

                <button class="btn btn-primary" type="submit">
                    Guardar dirección
                </button>
            </div>

        </form>
    </div>
</div>

@endsection