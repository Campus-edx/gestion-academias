{{-- Alta de una tarifa (SCRUM-356 y 357). Las tarifas no se modifican: se cierran y se crea otra. --}}
@extends('plantillas.principal')

@section('title', 'Nueva tarifa')
@section('topbar', 'Tarifas')

@section('content')

<div class="page-heading">
    <div>
        <h1>Nueva tarifa</h1>
    </div>
    <a class="btn btn-secondary" href="{{ route('tarifas.index') }}">Volver al listado</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('tarifas.store') }}">
            @csrf

            <div class="form-grid">
                {{-- Un solo desplegable para "de qué es": cada curso completo y cada módulo de cada curso.
                     El valor es "curso:5" o "modulo:12"; el controlador lo separa. --}}
                <div class="form-group full">
                    <label class="form-label" for="destino">Tarifa de</label>
                    <select class="form-select" id="destino" name="destino">
                        <option value="">Elige…</option>
                        @foreach($destinos as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('destino') === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    @error('destino')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" type="text" maxlength="120" value="{{ old('nombre') }}" placeholder="Mensualidad 2026-27">
                    @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="periodicidad">Periodicidad</label>
                    <select class="form-select" id="periodicidad" name="periodicidad">
                        @foreach($periodicidades as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('periodicidad', 'mensual') === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    @error('periodicidad')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- step="0.01" deja escribir céntimos. --}}
                <div class="form-group">
                    <label class="form-label" for="importe">Importe sin IVA (€)</label>
                    <input class="form-control" id="importe" name="importe" type="number" step="0.01" min="0" value="{{ old('importe') }}">
                    @error('importe')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="iva_porcentaje">IVA (%)</label>
                    <input class="form-control" id="iva_porcentaje" name="iva_porcentaje" type="number" step="0.01" min="0" max="100" value="{{ old('iva_porcentaje', '21') }}">
                    <div class="form-help">Pon 0 si la formación está exenta.</div>
                    @error('iva_porcentaje')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="vigente_desde">Válida desde</label>
                    <input class="form-control" id="vigente_desde" name="vigente_desde" type="date" value="{{ old('vigente_desde', now()->format('Y-m-d')) }}">
                    @error('vigente_desde')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="vigente_hasta">Válida hasta (opcional)</label>
                    <input class="form-control" id="vigente_hasta" name="vigente_hasta" type="date" value="{{ old('vigente_hasta') }}">
                    @error('vigente_hasta')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Crear tarifa</button>
            </div>
        </form>
    </div>
</div>
@endsection
