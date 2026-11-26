{{-- Alta y ficha de una empresa (SCRUM-175: 177, 178 y 179). --}}
@extends('plantillas.principal')

{{-- $empresa->exists: true en la ficha (editar); false en el alta. --}}
@section('title', $empresa->exists ? 'Ficha de la empresa' : 'Nueva empresa')
@section('topbar', 'Empresas')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $empresa->exists ? $empresa->nombreVisible() : 'Nueva empresa' }}</h1>
    </div>
    <a class="btn btn-secondary" href="{{ route('empresas.index') }}">Volver al listado</a>
</div>

<form method="POST" action="{{ $empresa->exists ? route('empresas.update', $empresa) : route('empresas.store') }}">
    @csrf
    @if($empresa->exists)
        @method('PUT')
    @endif

    {{-- ═══════════ 1. DATOS DE LA EMPRESA ═══════════ --}}
    <div class="card">
        <div class="card-body">
            <h2>Datos de la empresa</h2>
            <div class="form-grid">
                @foreach([
                    'razon_social' => ['Razón social', 'text', 180],
                    'nombre_comercial' => ['Nombre comercial', 'text', 180],
                    'cif' => ['CIF', 'text', 30],
                    'correo_electronico' => ['Correo electrónico', 'email', 160],
                    'telefono' => ['Teléfono', 'text', 40],
                ] as $campo => [$etiqueta, $tipo, $maximo])
                    <div class="form-group">
                        <label class="form-label" for="{{ $campo }}">{{ $etiqueta }}</label>
                        <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="{{ $tipo }}" maxlength="{{ $maximo }}"
                               value="{{ old($campo, $empresa->$campo) }}">
                        @error($campo)<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach

                {{-- Organización: opcional. Si se elige, hay que decir si es propietaria o colaboradora. --}}
                <div class="form-group">
                    <label class="form-label" for="organizacion_id">Organización</label>
                    <select class="form-select" id="organizacion_id" name="organizacion_id">
                        <option value="">Independiente (sin organización)</option>
                        @foreach($organizaciones as $organizacion)
                            <option value="{{ $organizacion->id }}" @selected((string) old('organizacion_id', $empresa->organizacion_id) === (string) $organizacion->id)>
                                {{ $organizacion->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('organizacion_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="tipo_vinculacion">Relación con la organización</label>
                    <select class="form-select" id="tipo_vinculacion" name="tipo_vinculacion">
                        <option value="">—</option>
                        <option value="propietaria" @selected(old('tipo_vinculacion', $empresa->tipo_vinculacion) === 'propietaria')>Propietaria (es del grupo)</option>
                        <option value="colaboradora" @selected(old('tipo_vinculacion', $empresa->tipo_vinculacion) === 'colaboradora')>Colaboradora</option>
                    </select>
                    <div class="form-help">Solo si tiene organización.</div>
                    @error('tipo_vinculacion')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Baja lógica (SCRUM-179): se desmarca, no se borra. --}}
                <div class="form-group full">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $empresa->activo))>
                        Activa (si se desmarca, sus usuarios ya no pueden entrar)
                    </label>
                    @error('activo')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ 2. APARIENCIA ═══════════ --}}
    <div class="card">
        <div class="card-body">
            <h2>Apariencia</h2>
            <p class="form-help">Lo que ven los usuarios de esta empresa: el nombre en el menú y en la pestaña, y el color de botones y enlaces.</p>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre_aplicacion">Nombre de la aplicación</label>
                    <input class="form-control" id="nombre_aplicacion" name="nombre_aplicacion" type="text" maxlength="80"
                           value="{{ old('nombre_aplicacion', $empresa->nombre_aplicacion) }}">
                    @error('nombre_aplicacion')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                {{-- type="color" abre un selector de color y envía el código, por ejemplo #0d6efd. --}}
                <div class="form-group">
                    <label class="form-label" for="color_principal">Color principal</label>
                    <input class="form-control" id="color_principal" name="color_principal" type="color"
                           value="{{ old('color_principal', $empresa->color_principal) }}">
                    @error('color_principal')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Quien solo tiene empresas.ver puede mirar, pero no guardar. --}}
    @can('empresas.editar')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">{{ $empresa->exists ? 'Guardar cambios' : 'Crear empresa' }}</button>
        </div>
    @endcan
</form>
@endsection
