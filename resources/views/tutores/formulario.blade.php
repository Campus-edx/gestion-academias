{{-- Ficha del tutor. Sirve para el alta y para editar. Al editar aparece la tarjeta
     "Alumnos a su cargo", donde se asocian y se quitan alumnos. --}}
@extends('plantillas.principal')

@section('title', $tutor->exists ? 'Ficha del tutor' : 'Nuevo tutor')
@section('topbar', 'Tutores')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $tutor->exists ? 'Ficha de ' . $persona->nombreCompleto() : 'Nuevo tutor' }}</h1>
        @unless($tutor->exists)
            <p>Si la persona ya está registrada (por ejemplo, como profesora), escribe su documento y se reutilizará su ficha.</p>
        @endunless
    </div>
    <a class="btn btn-secondary" href="{{ route('tutores.index') }}">Volver al listado</a>
</div>

{{-- ═══════════ 1. DATOS DEL TUTOR (SCRUM-210) ═══════════ --}}
<div class="card">
    <div class="card-body">
        <h2>Datos del tutor</h2>

        <form method="POST" action="{{ $tutor->exists ? route('tutores.update', $tutor) : route('tutores.store') }}">
            @csrf
            @if($tutor->exists)
                @method('PUT')
            @endif

            {{-- Si venimos de la ficha de un alumno, se envía su id para asociarlo después. --}}
            @if(!$tutor->exists && $alumnoId)
                <input type="hidden" name="alumno_id" value="{{ $alumnoId }}">
            @endif

            <div class="form-grid">
                @foreach([
                    'nombre' => 'Nombre',
                    'apellidos' => 'Apellidos',
                    'telefono' => 'Teléfono',
                    'correo_electronico' => 'Email',
                ] as $campo => $etiqueta)
                    <div class="form-group">
                        <label class="form-label" for="{{ $campo }}">{{ $etiqueta }}</label>
                        <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="text"
                               value="{{ old($campo, $persona->$campo) }}">
                        @error($campo)<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach

                <div class="form-group">
                    <label class="form-label" for="tipo_documento">Tipo de documento</label>
                    <select class="form-select" id="tipo_documento" name="tipo_documento">
                        <option value="">Sin documento</option>
                        @foreach(['dni' => 'DNI', 'nie' => 'NIE', 'pasaporte' => 'Pasaporte'] as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('tipo_documento', $persona->tipo_documento) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    @error('tipo_documento')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="numero_documento">Número de documento</label>
                    <input class="form-control" id="numero_documento" name="numero_documento" type="text"
                           value="{{ old('numero_documento', $persona->numero_documento) }}">
                    @error('numero_documento')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                           value="{{ old('fecha_nacimiento', $persona->fecha_nacimiento?->format('Y-m-d')) }}">
                    @error('fecha_nacimiento')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                @if(session('posible_duplicado'))
                    <div class="form-group">
                        <label class="checkbox-row">
                            <input type="checkbox" name="es_otra_persona" value="1">
                            Es otra persona: darla de alta igualmente
                        </label>
                    </div>
                @endif
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('tutores.index') }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar datos</button>
            </div>
        </form>
    </div>
</div>

@if($tutor->exists)

{{-- ═══════════ 2. ALUMNOS A SU CARGO (SCRUM-215) ═══════════ --}}
<div class="card" id="alumnos">
    <div class="card-body">
        <h2>Alumnos a su cargo</h2>

        @if($errors->asociar->any())
            <div class="alert alert-danger">
                @foreach($errors->asociar->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Alumno</th><th>Parentesco</th><th>Contacto principal</th><th>Puede recogerlo</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($alumnosACargo as $relacion)
                        <tr>
                            <td><a href="{{ route('alumnos.edit', $relacion->alumno) }}">{{ $relacion->alumno->persona->nombreCompleto() }}</a></td>
                            <td>{{ $relacion->parentesco->nombre }}</td>
                            <td>{{ $relacion->es_contacto_principal ? 'Sí' : 'No' }}</td>
                            <td>{{ $relacion->autorizado_recogida ? 'Sí' : 'No' }}</td>
                            <td class="actions">
                                <form method="POST" action="{{ route('tutores.alumnos.destroy', [$tutor, $relacion]) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">Todavía no tiene alumnos a su cargo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Formulario para asociar un alumno. Si venimos de la ficha de un alumno, sale ya elegido. --}}
        <form method="POST" action="{{ route('tutores.alumnos.store', $tutor) }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="alumno_id">Alumno</label>
                    <select class="form-select" id="alumno_id" name="alumno_id">
                        <option value="">Elige…</option>
                        @foreach($alumnos as $alumno)
                            <option value="{{ $alumno->id }}" @selected((string) old('alumno_id', $alumnoId) === (string) $alumno->id)>
                                {{ $alumno->persona->apellidos }}, {{ $alumno->persona->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="parentesco_id">Parentesco</label>
                    <select class="form-select" id="parentesco_id" name="parentesco_id">
                        <option value="">Elige…</option>
                        @foreach($parentescos as $parentesco)
                            <option value="{{ $parentesco->id }}" @selected((string) old('parentesco_id') === (string) $parentesco->id)>{{ $parentesco->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="es_contacto_principal" value="1" @checked(old('es_contacto_principal'))>
                        Contacto principal (si lo marcas, el anterior deja de serlo)
                    </label>
                    <label class="checkbox-row">
                        <input type="checkbox" name="autorizado_recogida" value="1" @checked(old('autorizado_recogida'))>
                        Puede recogerlo
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Asociar alumno</button>
            </div>
        </form>
    </div>
</div>

@endif
@endsection
