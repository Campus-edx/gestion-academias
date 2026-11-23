{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

@section('title', 'Usuarios')
@section('topbar', 'Usuarios')

@section('content')

<div class="page-heading">
    <div>
        <h1>Usuarios</h1>
        <p>Las personas que entran en la aplicación con tu empresa. Lo que puede hacer cada una lo dicen sus roles.</p>
    </div>
    {{-- @can: el botón solo sale a quien puede crear usuarios (permiso usuarios.editar). --}}
    @can('usuarios.editar')
        <a class="btn btn-primary" href="{{ route('usuarios.create') }}">Nuevo usuario</a>
    @endcan
</div>

{{-- Buscador. Formulario GET, sin @csrf (solo pide el listado filtrado). --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('usuarios.index') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label" for="buscar">Buscar por nombre o correo</label>
                <input class="form-control" id="buscar" name="buscar" type="text" value="{{ $buscar }}">
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" type="submit">Buscar</button>
                @if($buscar !== '')
                    <a class="btn btn-secondary" href="{{ route('usuarios.index') }}">Ver todos</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Correo electrónico</th><th>Roles (hoy)</th><th>Último acceso</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->nombre }}</td>
                        <td>{{ $usuario->correo_electronico }}</td>
                        {{-- Sus roles vigentes (los trae el controlador con with()). --}}
                        <td>
                            @forelse($usuario->roles as $usuarioRol)
                                <span class="badge badge-primary">{{ $usuarioRol->rol->nombre }}</span>
                            @empty
                                <span class="badge badge-muted">Sin rol</span>
                            @endforelse
                        </td>
                        {{-- ?->: si nunca ha entrado (vacío), no da error y sale la raya. --}}
                        <td>{{ $usuario->fecha_ultimo_acceso?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>
                            @if($usuario->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-muted">De baja</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('usuarios.edit', $usuario) }}">Ver / editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">
                        {{ $buscar !== '' ? 'Ningún usuario coincide con la búsqueda.' : 'Todavía no hay usuarios.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
