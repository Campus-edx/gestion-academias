{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

@section('title', 'Profesores')
@section('topbar', 'Profesores')

@section('content')

<div class="page-heading">
    <div>
        <h1>Profesores</h1>
        <p>Un profesor es el mismo aunque trabaje para varias empresas.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('profesores.create') }}">Nuevo profesor</a>
</div>

{{-- Filtros: buscador y empresa. Formulario GET, sin @csrf (solo pide el listado filtrado). --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('profesores.index') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label" for="buscar">Buscar por nombre, documento o especialidad</label>
                <input class="form-control" id="buscar" name="buscar" type="text" value="{{ $buscar }}">
            </div>
            {{-- SCRUM-246: ver solo los que trabajan ahora para una empresa. --}}
            <div class="form-group">
                <label class="form-label" for="empresa_id">Con vinculación vigente en</label>
                <select class="form-select" id="empresa_id" name="empresa_id">
                    <option value="">Todas las empresas</option>
                    @foreach($empresas as $empresa)
                        {{-- (string) porque lo que llega en la dirección es texto y el id es número. --}}
                        <option value="{{ $empresa->id }}" @selected((string) $empresaId === (string) $empresa->id)>
                            {{ $empresa->nombre_comercial ?: $empresa->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" type="submit">Filtrar</button>
                @if($buscar !== '' || $empresaId)
                    <a class="btn btn-secondary" href="{{ route('profesores.index') }}">Ver todos</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Apellidos y nombre</th><th>Especialidad</th><th>Empresas (hoy)</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($profesores as $profesor)
                    <tr>
                        <td>{{ $profesor->persona->apellidos }}, {{ $profesor->persona->nombre }}</td>
                        {{-- ?: '—' = si está vacío, pon una raya. --}}
                        <td>{{ $profesor->especialidad ?: '—' }}</td>
                        {{-- Sus vinculaciones vigentes: empresa y si es empleado o autónomo. --}}
                        <td>
                            @forelse($profesor->vinculacionesVigentes as $vinculacion)
                                <div>
                                    {{ $vinculacion->empresa->nombre_comercial ?: $vinculacion->empresa->razon_social }}
                                    <span class="badge badge-primary">{{ $vinculacion->tipo_vinculacion === 'autonomo' ? 'Autónomo' : 'Empleado' }}</span>
                                </div>
                            @empty
                                <span class="badge badge-muted">Sin vinculación</span>
                            @endforelse
                        </td>
                        <td>
                            @if($profesor->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-muted">De baja</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('profesores.edit', $profesor) }}">Ver / editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">
                        {{ ($buscar !== '' || $empresaId) ? 'Ningún profesor coincide con el filtro.' : 'Todavía no hay profesores.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
