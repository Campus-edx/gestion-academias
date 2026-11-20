{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

{{-- $profesor->exists: true en la ficha (editar); false en el alta. --}}
@section('title', $profesor->exists ? 'Ficha del profesor' : 'Nuevo profesor')
@section('topbar', 'Profesores')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $profesor->exists ? 'Ficha de ' . $persona->nombreCompleto() : 'Nuevo profesor' }}</h1>
        @unless($profesor->exists)
            <p>Si la persona ya está registrada (por ejemplo, como tutor o alumno), escribe su documento y se reutilizará su ficha.</p>
        @endunless
    </div>
    <a class="btn btn-secondary" href="{{ route('profesores.index') }}">Volver al listado</a>
</div>

{{-- ═══════════ 1. DATOS DEL PROFESOR (SCRUM-228 y 233) ═══════════ --}}
<div class="card">
    <div class="card-body">
        <h2>Datos del profesor</h2>

        <form method="POST" action="{{ $profesor->exists ? route('profesores.update', $profesor) : route('profesores.store') }}">
            @csrf
            @if($profesor->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                {{-- Datos personales (tabla personas), igual que en alumnos. --}}
                @foreach([
                    'nombre' => 'Nombre',
                    'apellidos' => 'Apellidos',
                    'correo_electronico' => 'Email',
                    'telefono' => 'Teléfono',
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

                {{-- Datos profesionales (tabla profesores). --}}
                <div class="form-group">
                    <label class="form-label" for="especialidad">Especialidad</label>
                    <input class="form-control" id="especialidad" name="especialidad" type="text"
                           value="{{ old('especialidad', $profesor->especialidad) }}">
                    @error('especialidad')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="iban">IBAN</label>
                    <input class="form-control" id="iban" name="iban" type="text"
                           value="{{ old('iban', $profesor->iban) }}">
                    @error('iban')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Casilla "Activo": desmarcarla es dar de baja al profesor (no se borra). --}}
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $profesor->activo))>
                        Activo
                    </label>
                </div>

                {{-- Módulos que puede impartir (tabla profesor_modulo).
                     name="modulos[]": los corchetes hacen que lleguen todas las marcadas como una lista.
                     in_array: marca la casilla si ese módulo está en la lista de marcados. --}}
                <div class="form-group full">
                    <label class="form-label">Módulos que puede impartir</label>
                    @forelse($modulos as $modulo)
                        <label class="checkbox-row">
                            <input type="checkbox" name="modulos[]" value="{{ $modulo->id }}"
                                   @checked(in_array($modulo->id, old('modulos', $modulosMarcados)))>
                            {{ $modulo->nombre }}
                        </label>
                    @empty
                        <p class="form-help">Todavía no hay módulos. Se crean con los cursos.</p>
                    @endforelse
                    @error('modulos.*')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Centros en los que puede trabajar (tabla profesor_centro). --}}
                <div class="form-group full">
                    <label class="form-label">Centros en los que puede trabajar</label>
                    @forelse($centros as $centro)
                        <label class="checkbox-row">
                            <input type="checkbox" name="centros[]" value="{{ $centro->id }}"
                                   @checked(in_array($centro->id, old('centros', $centrosMarcados)))>
                            {{ $centro->nombre }}
                        </label>
                    @empty
                        <p class="form-help">Todavía no hay centros.</p>
                    @endforelse
                    @error('centros.*')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Solo aparece si el controlador avisó de un posible duplicado. --}}
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
                <a class="btn btn-secondary" href="{{ route('profesores.index') }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar datos</button>
            </div>
        </form>
    </div>
</div>

{{-- Las dos partes siguientes solo existen cuando el profesor ya está guardado. --}}
@if($profesor->exists)

{{-- ═══════════ 2. VINCULACIÓN CON EMPRESAS (SCRUM-237 y 242) ═══════════ --}}
<div class="card" id="vinculaciones">
    <div class="card-body">
        <h2>Vinculación con empresas</h2>
        <p class="form-help">Para cambiar el tipo o el precio, cierra la vinculación actual y añade otra: así queda el historial.</p>

        {{-- Errores de estos formularios. Van en sus propios "sacos" (vinculacion y cerrar). --}}
        @foreach(['vinculacion', 'cerrar'] as $saco)
            @if($errors->$saco->any())
                <div class="alert alert-danger">
                    @foreach($errors->$saco->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif
        @endforeach

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Empresa</th><th>Tipo</th><th>Precio/hora</th><th>IRPF</th><th>Desde</th><th>Hasta</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($vinculaciones as $vinculacion)
                        <tr>
                            <td>{{ $vinculacion->empresa->nombre_comercial ?: $vinculacion->empresa->razon_social }}</td>
                            <td>{{ $vinculacion->tipo_vinculacion === 'autonomo' ? 'Autónomo' : 'Empleado' }}</td>
                            {{-- number_format: 18.5 → 18,50 --}}
                            <td>{{ $vinculacion->precio_hora !== null ? number_format($vinculacion->precio_hora, 2, ',', '.') . ' €' : '—' }}</td>
                            <td>{{ $vinculacion->irpf_porcentaje !== null ? number_format($vinculacion->irpf_porcentaje, 2, ',', '.') . ' %' : '—' }}</td>
                            <td>{{ $vinculacion->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $vinculacion->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($vinculacion->estaVigente())
                                    <span class="badge badge-success">Vigente</span>
                                @elseif($vinculacion->fecha_fin === null || $vinculacion->fecha_fin->isFuture())
                                    <span class="badge badge-primary">Futura</span>
                                @else
                                    <span class="badge badge-muted">Cerrada</span>
                                @endif
                            </td>
                            {{-- Cerrar: solo las que no tienen fecha de fin. --}}
                            <td class="actions">
                                @if($vinculacion->fecha_fin === null)
                                    <form method="POST" action="{{ route('profesores.vinculaciones.cerrar', [$profesor, $vinculacion]) }}" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <input class="form-control" type="date" name="fecha_fin" value="{{ now()->format('Y-m-d') }}" style="width:auto; display:inline-block;">
                                        <button class="btn btn-danger btn-sm" type="submit">Cerrar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state">Todavía no tiene ninguna vinculación.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Formulario para añadir una vinculación. --}}
        <form method="POST" action="{{ route('profesores.vinculaciones.store', $profesor) }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="empresa_id">Empresa</label>
                    <select class="form-select" id="empresa_id" name="empresa_id">
                        <option value="">Elige…</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @selected((string) old('empresa_id') === (string) $empresa->id)>
                                {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tipo_vinculacion">Tipo</label>
                    <select class="form-select" id="tipo_vinculacion" name="tipo_vinculacion">
                        <option value="empleado" @selected(old('tipo_vinculacion') === 'empleado')>Empleado</option>
                        <option value="autonomo" @selected(old('tipo_vinculacion') === 'autonomo')>Autónomo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="precio_hora">Precio por hora (€)</label>
                    {{-- step="0.01" deja escribir céntimos. --}}
                    <input class="form-control" id="precio_hora" name="precio_hora" type="number" step="0.01" min="0" value="{{ old('precio_hora') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="irpf_porcentaje">IRPF (%)</label>
                    <input class="form-control" id="irpf_porcentaje" name="irpf_porcentaje" type="number" step="0.01" min="0" max="100" value="{{ old('irpf_porcentaje') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_inicio">Desde</label>
                    <input class="form-control" id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Añadir vinculación</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════ 3. DISPONIBILIDAD HORARIA (SCRUM-250) ═══════════ --}}
<div class="card" id="disponibilidad">
    <div class="card-body">
        <h2>Disponibilidad horaria</h2>
        <p class="form-help">Franjas de la semana en las que puede dar clase. Las fechas son opcionales.</p>

        @if($errors->disponibilidad->any())
            <div class="alert alert-danger">
                @foreach($errors->disponibilidad->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Día</th><th>Horario</th><th>Desde</th><th>Hasta</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($disponibilidades as $franja)
                        <tr>
                            <td>{{ $franja->nombreDia() }}</td>
                            {{-- substr(…, 0, 5): de "17:00:00" se queda con "17:00". --}}
                            <td>{{ substr($franja->hora_inicio, 0, 5) }} – {{ substr($franja->hora_fin, 0, 5) }}</td>
                            <td>{{ $franja->fecha_inicio?->format('d/m/Y') ?? 'Siempre' }}</td>
                            <td>{{ $franja->fecha_fin?->format('d/m/Y') ?? 'Siempre' }}</td>
                            <td class="actions">
                                <form method="POST" action="{{ route('profesores.disponibilidad.destroy', [$profesor, $franja]) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">Todavía no tiene franjas de disponibilidad.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('profesores.disponibilidad.store', $profesor) }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="dia_semana">Día</label>
                    <select class="form-select" id="dia_semana" name="dia_semana">
                        @foreach([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'] as $numero => $dia)
                            <option value="{{ $numero }}" @selected((string) old('dia_semana') === (string) $numero)>{{ $dia }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- type="time" muestra un selector de hora y envía "17:00". --}}
                <div class="form-group">
                    <label class="form-label" for="hora_inicio">De</label>
                    <input class="form-control" id="hora_inicio" name="hora_inicio" type="time" value="{{ old('hora_inicio') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="hora_fin">A</label>
                    <input class="form-control" id="hora_fin" name="hora_fin" type="time" value="{{ old('hora_fin') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="desde">Desde (opcional)</label>
                    <input class="form-control" id="desde" name="desde" type="date" value="{{ old('desde') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="hasta">Hasta (opcional)</label>
                    <input class="form-control" id="hasta" name="hasta" type="date" value="{{ old('hasta') }}">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Añadir franja</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════ 4. AUSENCIAS (SCRUM-152 · HU-056) ═══════════ --}}
<div class="card" id="ausencias">
    <div class="card-body">
        <h2>Ausencias</h2>
        <p class="form-help">Vacaciones, bajas y otros días en que no puede dar clase. Esos días no se le pueden poner sesiones.</p>

        @if($errors->ausencias->any())
            <div class="alert alert-danger">
                @foreach($errors->ausencias->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Motivo</th><th>Desde</th><th>Hasta</th><th>Observaciones</th><th>Sesiones afectadas</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($ausencias as $ausencia)
                        <tr>
                            <td>{{ $ausencia->motivo }}</td>
                            <td>{{ $ausencia->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $ausencia->fecha_fin->format('d/m/Y') }}</td>
                            <td>{{ $ausencia->observaciones }}</td>
                            <td>
                                {{-- Las clases que ya tenía programadas esos días: hay que darlas a otro profesor. --}}
                                @forelse($afectadas[$ausencia->id] as $sesion)
                                    <div>
                                        <span class="badge badge-primary">{{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }}
                                            {{ substr($sesion->hora_inicio, 0, 5) }}–{{ substr($sesion->hora_fin, 0, 5) }}</span>
                                        {{ $sesion->grupos ?? 'Sin grupo' }} ({{ $sesion->empresa }})
                                    </div>
                                @empty
                                    <span class="badge badge-muted">Ninguna</span>
                                @endforelse
                            </td>
                            <td class="actions">
                                <form method="POST" action="{{ route('profesores.ausencias.destroy', [$profesor, $ausencia]) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">No tiene ausencias registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('profesores.ausencias.store', $profesor) }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="motivo_ausencia_id">Motivo</label>
                    <select class="form-select" id="motivo_ausencia_id" name="motivo_ausencia_id">
                        <option value="">Elige…</option>
                        @foreach($motivosAusencia as $motivo)
                            <option value="{{ $motivo->id }}" @selected((string) old('motivo_ausencia_id') === (string) $motivo->id)>{{ $motivo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Se llaman primer_dia y ultimo_dia (y no fecha_inicio) para no mezclarse con el formulario de vinculaciones. --}}
                <div class="form-group">
                    <label class="form-label" for="primer_dia">Primer día</label>
                    <input class="form-control" id="primer_dia" name="primer_dia" type="date" value="{{ old('primer_dia') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="ultimo_dia">Último día</label>
                    <input class="form-control" id="ultimo_dia" name="ultimo_dia" type="date" value="{{ old('ultimo_dia') }}">
                </div>
                <div class="form-group full">
                    <label class="form-label" for="observaciones_ausencia">Observaciones (opcional)</label>
                    <input class="form-control" id="observaciones_ausencia" name="observaciones" type="text" maxlength="255" value="{{ old('observaciones') }}">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Añadir ausencia</button>
            </div>
        </form>
    </div>
</div>

@endif
@endsection
