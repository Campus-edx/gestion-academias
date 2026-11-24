
@extends('plantillas.principal')

@section('title', isset($temporada->id) ? 'Editar temporada' : 'Nueva temporada')
@section('topbar', 'Temporadas')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ isset($temporada->id) ? 'Editar temporada' : 'Nueva temporada' }}</h1>
        <p>Completa los datos de la temporada.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <form
            action="{{ isset($temporada->id)
                ? route('temporadas.update', $temporada->id)
                : route('temporadas.store') }}"
            method="POST">

            @csrf

            @if(isset($temporada->id))
                @method('PUT')
            @endif

            <div class="form-grid">

                {{-- Empresa --}}
                <div class="form-group">
                    <label for="empresa_id">Empresa</label>

                    <select
                        name="empresa_id"
                        id="empresa_id"
                        class="form-control"
                        required>

                        <option value="">— Seleccionar empresa —</option>

                        @foreach($empresas as $empresa)
                            <option
                                value="{{ $empresa->id }}"
                                @selected(old('empresa_id', $temporada->empresa_id) == $empresa->id)>
                                {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
                            </option>
                        @endforeach

                    </select>

                    @error('empresa_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nombre --}}
                <div class="form-group">
                    <label for="nombre">Nombre de la temporada</label>

                    <input
                        type="text"
                        name="nombre"
                        id="nombre"
                        class="form-control"
                        value="{{ old('nombre', $temporada->nombre) }}"
                        maxlength="80"
                        required>

                    @error('nombre')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Fecha de inicio --}}
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de inicio</label>

                    <input
                        type="date"
                        name="fecha_inicio"
                        id="fecha_inicio"
                        class="form-control"
                        value="{{ old('fecha_inicio', $temporada->fecha_inicio) }}"
                        required>

                    @error('fecha_inicio')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Fecha de fin --}}
                <div class="form-group">
                    <label for="fecha_fin">Fecha de fin</label>

                    <input
                        type="date"
                        name="fecha_fin"
                        id="fecha_fin"
                        class="form-control"
                        value="{{ old('fecha_fin', $temporada->fecha_fin) }}"
                        required>

                    @error('fecha_fin')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Estado --}}
                <div class="form-group">
                    <label for="estado">Estado</label>

                    <select
                        name="estado"
                        id="estado"
                        class="form-control"
                        required>

                        <option value="planificada" @selected(old('estado', $temporada->estado ?? 'planificada') == 'planificada')>
                            Planificada
                        </option>

                        <option value="activa" @selected(old('estado', $temporada->estado) == 'activa')>
                            Activa
                        </option>

                        <option value="cerrada" @selected(old('estado', $temporada->estado) == 'cerrada')>
                            Cerrada
                        </option>

                    </select>

                    @error('estado')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Observaciones --}}
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>

                    <textarea
                        name="observaciones"
                        id="observaciones"
                        maxlength="255"
                        class="form-control"
                        rows="4">{{ old('observaciones', $temporada->observaciones) }}</textarea>

                    @error('observaciones')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            {{-- Botones --}}
            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    Guardar
                </button>

                <a href="{{ route('temporadas.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </div>
</div>

@endsection