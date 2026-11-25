{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

{{-- $alumno->exists: true si el alumno ya está guardado (editar); false si es nuevo (alta). --}}
@section('title', $alumno->exists ? 'Ficha del alumno' : 'Nuevo alumno')
@section('topbar', 'Alumnos')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $alumno->exists ? 'Ficha de ' . $persona->nombreCompleto() : 'Nuevo alumno' }}</h1>
        @unless($alumno->exists)
            <p>Si la persona ya está registrada (por ejemplo, como tutor), escribe su documento y se reutilizará su ficha.</p>
        @endunless
    </div>
</div>

<div class="card">
    <div class="card-body">

        {{-- El mismo formulario sirve para crear y para editar:
             si el alumno existe se envía a alumnos.update; si es nuevo, a alumnos.store. --}}
        <form method="POST" action="{{ $alumno->exists ? route('alumnos.update', $alumno) : route('alumnos.store') }}">
            {{-- Código de seguridad obligatorio en todos los formularios que envían datos. --}}
            @csrf
            {{-- Al editar, el envío se convierte en PUT (modificar). --}}
            @if($alumno->exists)
                @method('PUT')
            @endif

            <div class="form-grid">

                {{-- Campos de texto de la persona. A la izquierda, el nombre del campo en la base
                     de datos; a la derecha, el texto que ve el usuario. --}}
                @foreach([
                    'nombre' => 'Nombre',
                    'apellidos' => 'Apellidos',
                    'correo_electronico' => 'Email',
                    'telefono' => 'Teléfono',
                ] as $campo => $etiqueta)
                    <div class="form-group">
                        <label class="form-label" for="{{ $campo }}">{{ $etiqueta }}</label>
                        {{-- old(): si hubo un error, recupera lo escrito; si no, el valor guardado. --}}
                        <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="text"
                               value="{{ old($campo, $persona->$campo) }}">
                        @error($campo)<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach

                {{-- Tipo de documento: desplegable. La primera opción (vacía) es "sin documento". --}}
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

                {{-- type="date" muestra un calendario. Necesita la fecha en formato 2012-03-05 (Y-m-d). --}}
                <div class="form-group">
                    <label class="form-label" for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                           value="{{ old('fecha_nacimiento', $persona->fecha_nacimiento?->format('Y-m-d')) }}">
                    @error('fecha_nacimiento')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Observaciones: textarea es un cuadro de texto de varias líneas. La clase "full"
                     hace que ocupe todo el ancho. --}}
                <div class="form-group full">
                    <label class="form-label" for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $alumno->observaciones) }}</textarea>
                    @error('observaciones')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Casilla "Activo". Desmarcarla es dar de BAJA al alumno (no se borra). --}}
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $alumno->activo))>
                        Activo
                    </label>
                </div>

                {{-- Solo aparece si el controlador avisó de un posible duplicado (misma persona sin documento). --}}
                @if(session('posible_duplicado'))
                    <div class="form-group">
                        <label class="checkbox-row">
                            <input type="checkbox" name="es_otra_persona" value="1">
                            Es otra persona: darla de alta igualmente
                        </label>
                    </div>
                @endif
            </div>

            {{-- Botones: Cancelar vuelve al listado sin guardar; Guardar envía el formulario. --}}
            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('alumnos.index') }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>



{{-- NUEVO (capítulo de tutores): tarjeta con los tutores del alumno. Solo en la ficha (alumno ya guardado).
     Los tutores se asocian desde la ficha del tutor; aquí se ven y se enlaza a ellos. --}}
@if($alumno->exists)
<div class="card" id="tutores">
    <div class="card-body">
        <div class="page-heading">
            <div>
                <h2>Tutores</h2>
                {{-- SCRUM-220: quién puede pagar sus matrículas. Se elige al matricularlo. --}}
                <p class="form-help">Al matricularlo, puede pagar el propio alumno o cualquiera de estos tutores.</p>
            </div>
            {{-- Va al alta de tutor recordando este alumno (?alumno_id=…), para asociarlo después. --}}
            <a class="btn btn-secondary" href="{{ route('tutores.create', ['alumno_id' => $alumno->id]) }}">Nuevo tutor</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Tutor</th><th>Parentesco</th><th>Teléfono</th><th>Contacto principal</th><th>Puede recogerlo</th></tr>
                </thead>
                <tbody>
                    @forelse($tutoresDelAlumno as $relacion)
                        <tr>
                            {{-- El nombre es un enlace a la ficha del tutor. --}}
                            <td><a href="{{ route('tutores.edit', $relacion->tutor) }}">{{ $relacion->tutor->persona->nombreCompleto() }}</a></td>
                            <td>{{ $relacion->parentesco->nombre }}</td>
                            <td>{{ $relacion->tutor->persona->telefono ?: '—' }}</td>
                            <td>{{ $relacion->es_contacto_principal ? 'Sí' : 'No' }}</td>
                            <td>{{ $relacion->autorizado_recogida ? 'Sí' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">No tiene tutores. Si el tutor ya existe, ábrelo en Tutores y asócialo desde su ficha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- PENDIENTE: quitar "false &&" en el capítulo de Inscripciones. --}}
@if(false && $alumno->exists)

{{-- NUEVO (capítulo de inscripciones): matrículas del alumno (SCRUM-273, 278 y 282). --}}
<div class="card" id="inscripciones">
    <div class="card-body">
        <div class="page-heading">
            <div>
                <h2>Inscripciones</h2>
            </div>
            {{-- Solo se puede matricular a un alumno activo. --}}
            @if($alumno->activo)
                <a class="btn btn-primary" href="{{ route('inscripciones.create', ['alumno_id' => $alumno->id]) }}">Matricular</a>
            @endif
        </div>

        {{-- Errores del formulario de baja (saco "baja"). --}}
        @if($errors->baja->any())
            <div class="alert alert-danger">
                @foreach($errors->baja->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Curso · grupo</th><th>Centro</th><th>Paga</th><th>Alta</th><th>Baja</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($inscripciones as $inscripcion)
                        <tr>
                            <td>{{ $inscripcion->curso }} · {{ $inscripcion->grupo }}</td>
                            <td>{{ $inscripcion->centro }}</td>
                            <td>{{ $inscripcion->pagador_nombre }} {{ $inscripcion->pagador_apellidos }}</td>
                            {{-- Las fechas llegan como texto (2026-09-14): \Carbon\Carbon::parse las convierte para darles formato. --}}
                            <td>{{ \Carbon\Carbon::parse($inscripcion->fecha_alta)->format('d/m/Y') }}</td>
                            <td>{{ $inscripcion->fecha_baja ? \Carbon\Carbon::parse($inscripcion->fecha_baja)->format('d/m/Y') : '—' }}</td>
                            <td>
                                @if($inscripcion->estado === 'activa')
                                    <span class="badge badge-success">Activa</span>
                                @elseif($inscripcion->estado === 'baja')
                                    <span class="badge badge-muted">Baja</span>
                                @else
                                    <span class="badge badge-primary">Finalizada</span>
                                @endif
                            </td>
                            {{-- Dar de baja: solo las activas. --}}
                            <td class="actions">
                                @if($inscripcion->estado === 'activa')
                                    <form method="POST" action="{{ route('inscripciones.baja', $inscripcion->id) }}" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <input class="form-control" type="date" name="fecha_baja" value="{{ now()->format('Y-m-d') }}" style="width:auto; display:inline-block;">
                                        <button class="btn btn-danger btn-sm" type="submit">Dar de baja</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">No tiene inscripciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
