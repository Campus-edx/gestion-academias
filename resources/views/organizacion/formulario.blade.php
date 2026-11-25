{{-- Datos de la organización central (SCRUM-170). Una sola pantalla para crearla o modificarla. --}}
@extends('plantillas.principal')

@section('title', 'Organización')
@section('topbar', 'Organización')

@section('content')

<div class="page-heading">
    <div>
        <h1>Organización</h1>
        <p>El grupo que está por encima de todas las empresas.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        {{-- Siempre PUT: el controlador decide si crea o modifica. --}}
        <form method="POST" action="{{ route('organizacion.update') }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                {{-- Los cuatro campos son iguales: se pintan con un bucle. --}}
                @foreach([
                    'nombre' => ['Nombre', 'text', 180],
                    'cif' => ['CIF', 'text', 30],
                    'correo_electronico' => ['Correo electrónico', 'email', 160],
                    'telefono' => ['Teléfono', 'text', 40],
                ] as $campo => [$etiqueta, $tipo, $maximo])
                    <div class="form-group">
                        <label class="form-label" for="{{ $campo }}">{{ $etiqueta }}</label>
                        <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="{{ $tipo }}" maxlength="{{ $maximo }}"
                               value="{{ old($campo, $organizacion->$campo) }}">
                        @error($campo)<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">{{ $organizacion->exists ? 'Guardar cambios' : 'Registrar organización' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection