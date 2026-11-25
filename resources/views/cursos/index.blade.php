{{-- Listado de los cursos de la empresa, con sus horas totales (de la vista v_cursos_horas). --}}
@extends('plantillas.principal')

@section('title', 'Cursos')
@section('topbar', 'Cursos')

@section('content')

<div class="page-heading">
    <div>
        <h1>Cursos</h1>
        <p>La oferta de la empresa. Las fechas no van aquí: van en cada grupo.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('cursos.create') }}">Nuevo curso</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Categoría</th><th>Modalidad</th><th>Alumnos por grupo</th><th>Módulos</th><th>Horas</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($cursos as $curso)
                    <tr>
                        <td>{{ $curso->nombre }}</td>
                        <td>{{ $curso->categoria->nombre }}</td>
                        {{-- ucfirst: presencial → Presencial. --}}
                        <td>{{ ucfirst($curso->modalidad) }}</td>
                        <td>{{ $curso->min_alumnos }} a {{ $curso->max_alumnos }}</td>
                        {{-- numero_modulos y horas_totales vienen de la vista; ?? 0 por si no tiene módulos. --}}
                        <td>{{ $curso->numero_modulos ?? 0 }}</td>
                        <td>{{ $curso->horas_totales ?? 0 }} h</td>
                        <td>
                            @if($curso->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-muted">Inactivo</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('cursos.edit', $curso) }}">Ver / editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">Todavía no hay cursos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
