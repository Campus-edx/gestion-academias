{{-- Alta y edición de un descuento. --}}
@extends('plantillas.principal')

@section('title', $descuento->exists ? 'Editar descuento' : 'Nuevo descuento')
@section('topbar', 'Descuentos')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $descuento->exists ? 'Editar ' . $descuento->nombre : 'Nuevo descuento' }}</h1>
    </div>
    <a class="btn btn-secondary" href="{{ route('descuentos.index') }}">Volver al listado</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $descuento->exists ? route('descuentos.update', $descuento) : route('descuentos.store') }}">
            @csrf
            @if($descuento->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" type="text" maxlength="120" value="{{ old('nombre', $descuento->nombre) }}" placeholder="Hermanos">
                    @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="porcentaje" @selected(old('tipo', $descuento->tipo) === 'porcentaje')>Porcentaje (%)</option>
                        <option value="importe" @selected(old('tipo', $descuento->tipo) === 'importe')>Importe fijo (€)</option>
                    </select>
                    @error('tipo')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="valor">Valor</label>
                    <input class="form-control" id="valor" name="valor" type="number" step="0.01" min="0" value="{{ old('valor', $descuento->valor) }}">
                    <div class="form-help">10 con porcentaje = un 10 %. 10 con importe = 10 euros.</div>
                    @error('valor')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group full">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $descuento->activo))>
                        Activo (se ofrece al matricular)
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">{{ $descuento->exists ? 'Guardar cambios' : 'Crear descuento' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
