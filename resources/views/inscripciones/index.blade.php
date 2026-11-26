{{-- Historial de inscripciones de la empresa, con filtros (SCRUM-282). --}}
@extends('plantillas.principal')

@section('title', 'Inscripciones')
@section('topbar', 'Inscripciones')

@section('content')

<div class="page-heading">
    <div>
        <h1>Inscripciones</h1>
        <p>Para matricular a alguien, abre su ficha de alumno y pulsa "Matricular".</p>
    </div>
</div>

{{-- Filtros. Formulario GET: los filtros viajan en la dirección (/inscripciones?estado=anteriores&curso_id=3). --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('inscripciones.index') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label" for="estado">Mostrar</label>
                <select class="form-select" id="estado" name="estado">
                    @foreach(['activas' => 'Activas', 'anteriores' => 'Anteriores (bajas y finalizadas)', 'anuladas' => 'Anuladas (hechas por error)', 'todas' => 'Todas'] as $valor => $texto)
                        <option value="{{ $valor }}" @selected($estado === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="curso_id">Curso</label>
                <select class="form-select" id="curso_id" name="curso_id">
                    <option value="">Todos</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" @selected((string) $cursoId === (string) $curso->id)>{{ $curso->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="buscar">Alumno</label>
                <input class="form-control" id="buscar" name="buscar" type="text" value="{{ $buscar }}">
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" type="submit">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Alumno</th><th>Curso · grupo</th><th>Centro</th><th>Paga</th><th>Alta</th><th>Baja</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @forelse($inscripciones as $inscripcion)
                    <tr>
                        {{-- El nombre lleva a la ficha del alumno (allí se da de baja). --}}
                        <td><a href="{{ route('alumnos.edit', $inscripcion->alumno_id) }}">{{ $inscripcion->alumno_apellidos }}, {{ $inscripcion->alumno_nombre }}</a></td>
                        <td>{{ $inscripcion->curso }} · {{ $inscripcion->grupo }}</td>
                        <td>{{ $inscripcion->centro }}</td>
                        <td>{{ $inscripcion->pagador_nombre }} {{ $inscripcion->pagador_apellidos }}</td>
                        <td>{{ \Carbon\Carbon::parse($inscripcion->fecha_alta)->format('d/m/Y') }}</td>
                        <td>{{ $inscripcion->fecha_baja ? \Carbon\Carbon::parse($inscripcion->fecha_baja)->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if($inscripcion->estado === 'activa')
                                <span class="badge badge-success">Activa</span>
                            @elseif($inscripcion->estado === 'baja')
                                <span class="badge badge-muted">Baja</span>
                            @elseif($inscripcion->estado === 'anulada')
                                <span class="badge badge-muted">Anulada</span>
                            @else
                                <span class="badge badge-primary">Finalizada</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No hay inscripciones con estos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
