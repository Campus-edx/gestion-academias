{{-- Listado de empresas (SCRUM-175). --}}
@extends('plantillas.principal')

@section('title', 'Empresas')
@section('topbar', 'Empresas')

@section('content')

<div class="page-heading">
    <div>
        <h1>Empresas</h1>
        <p>Todas las empresas de la aplicación, propias y colaboradoras.</p>
    </div>
    @can('empresas.editar')
        <a class="btn btn-primary" href="{{ route('empresas.create') }}">Nueva empresa</a>
    @endcan
</div>

{{-- Buscador. Formulario GET, sin @csrf. --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('empresas.index') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label" for="buscar">Buscar por nombre o CIF</label>
                <input class="form-control" id="buscar" name="buscar" type="text" value="{{ $buscar }}">
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" type="submit">Buscar</button>
                @if($buscar !== '')
                    <a class="btn btn-secondary" href="{{ route('empresas.index') }}">Ver todas</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Empresa</th><th>CIF</th><th>Organización</th><th>Usuarios</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($empresas as $empresa)
                    <tr>
                        <td>
                            {{ $empresa->nombreVisible() }}
                            {{-- La razón social debajo, solo si es distinta del nombre que se muestra. --}}
                            @if($empresa->nombre_comercial && $empresa->nombre_comercial !== $empresa->razon_social)
                                <div class="form-help">{{ $empresa->razon_social }}</div>
                            @endif
                        </td>
                        <td>{{ $empresa->cif ?: '—' }}</td>
                        <td>
                            @if($empresa->organizacion)
                                {{ $empresa->organizacion->nombre }}
                                <span class="badge badge-primary">{{ $empresa->tipo_vinculacion === 'propietaria' ? 'Propietaria' : 'Colaboradora' }}</span>
                            @else
                                <span class="badge badge-muted">Independiente</span>
                            @endif
                        </td>
                        {{-- usuarios_count lo añade withCount('usuarios') en el controlador. --}}
                        <td>{{ $empresa->usuarios_count }}</td>
                        <td>
                            @if($empresa->activo)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-muted">De baja</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('empresas.edit', $empresa) }}">Ver / editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">
                        {{ $buscar !== '' ? 'Ninguna empresa coincide con la búsqueda.' : 'Todavía no hay empresas.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
