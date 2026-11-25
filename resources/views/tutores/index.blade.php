{{-- Listado de tutores, con buscador y los alumnos a cargo de cada uno. --}}
@extends('plantillas.principal')

@section('title', 'Tutores')
@section('topbar', 'Tutores')

@section('content')

<div class="page-heading">
    <div>
        <h1>Tutores</h1>
        <p>Padres, madres y tutores legales. Un tutor puede tener varios alumnos a su cargo.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('tutores.create') }}">Nuevo tutor</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('tutores.index') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label" for="buscar">Buscar por nombre, documento o teléfono</label>
                <input class="form-control" id="buscar" name="buscar" type="text" value="{{ $buscar }}">
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" type="submit">Buscar</button>
                @if($buscar !== '')
                    <a class="btn btn-secondary" href="{{ route('tutores.index') }}">Ver todos</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Apellidos y nombre</th><th>Teléfono</th><th>Alumnos a su cargo</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($tutores as $tutor)
                    <tr>
                        <td>{{ $tutor->persona->apellidos }}, {{ $tutor->persona->nombre }}</td>
                        <td>{{ $tutor->persona->telefono ?: '—' }}</td>
                        {{-- Un renglón por alumno a cargo. --}}
                        <td>
                            @forelse($tutor->alumnosACargo as $relacion)
                                <div>{{ $relacion->alumno->persona->nombreCompleto() }}</div>
                            @empty
                                <span class="badge badge-muted">Ninguno</span>
                            @endforelse
                        </td>
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('tutores.edit', $tutor) }}">Ver / editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">
                        {{ $buscar !== '' ? 'Ningún tutor coincide con la búsqueda.' : 'Todavía no hay tutores.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
