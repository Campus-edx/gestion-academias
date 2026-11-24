{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

{{-- $usuario->exists: true en la ficha (editar); false en el alta. --}}
@section('title', $usuario->exists ? 'Ficha del usuario' : 'Nuevo usuario')
@section('topbar', 'Usuarios')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $usuario->exists ? 'Ficha de ' . $usuario->nombre : 'Nuevo usuario' }}</h1>
        @unless($usuario->exists)
            <p>Después de crearlo, dale al menos un rol: sin rol no podrá trabajar.</p>
        @endunless
    </div>
    <a class="btn btn-secondary" href="{{ route('usuarios.index') }}">Volver al listado</a>
</div>

{{-- ═══════════ 1. DATOS DEL USUARIO (SCRUM-181 y 185) ═══════════ --}}
<div class="card">
    <div class="card-body">
        <h2>Datos del usuario</h2>

        <form method="POST" action="{{ $usuario->exists ? route('usuarios.update', $usuario) : route('usuarios.store') }}">
            @csrf
            @if($usuario->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" type="text" maxlength="120"
                           value="{{ old('nombre', $usuario->nombre) }}">
                    @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="correo_electronico">Correo electrónico (con él inicia sesión)</label>
                    <input class="form-control" id="correo_electronico" name="correo_electronico" type="email" maxlength="160"
                           value="{{ old('correo_electronico', $usuario->correo_electronico) }}">
                    @error('correo_electronico')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- La contraseña NUNCA se rellena con old() ni con la guardada: siempre sale vacía. --}}
                <div class="form-group">
                    <label class="form-label" for="contrasena">
                        {{ $usuario->exists ? 'Nueva contraseña (déjala vacía para no cambiarla)' : 'Contraseña' }}
                    </label>
                    <input class="form-control" id="contrasena" name="contrasena" type="password" autocomplete="new-password">
                    @error('contrasena')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- "contrasena_confirmation": la regla "confirmed" busca un campo con este nombre exacto. --}}
                <div class="form-group">
                    <label class="form-label" for="contrasena_confirmation">Repite la contraseña</label>
                    <input class="form-control" id="contrasena_confirmation" name="contrasena_confirmation" type="password" autocomplete="new-password">
                </div>

                {{-- Casilla "Activo". Si se desmarca, el usuario ya no puede entrar. --}}
                <div class="form-group full">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario->activo))>
                        Activo (puede iniciar sesión)
                    </label>
                    @error('activo')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Quien solo tiene usuarios.ver puede mirar la ficha, pero no ve el botón de guardar. --}}
            @can('usuarios.editar')
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">{{ $usuario->exists ? 'Guardar cambios' : 'Crear usuario' }}</button>
                </div>
            @endcan
        </form>
    </div>
</div>

{{-- Lo de abajo solo sale en la ficha: para dar roles, el usuario tiene que existir. --}}
@if($usuario->exists)

{{-- ═══════════ 2. ROLES (SCRUM-190) ═══════════ --}}
<div class="card" id="roles">
    <div class="card-body">
        <h2>Roles</h2>
        <p class="form-help">Un rol no se borra: se cierra con su fecha de fin. Si se elige un centro, el rol solo vale en ese centro.</p>

        {{-- Errores de esta tarjeta (bolsas "rol" y "cerrar"). --}}
        @foreach(['rol', 'cerrar'] as $bolsa)
            @if($errors->$bolsa->any())
                <div class="alert alert-danger">
                    @foreach($errors->$bolsa->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif
        @endforeach

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Rol</th><th>Centro</th><th>Desde</th><th>Hasta</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($rolesDelUsuario as $usuarioRol)
                        <tr>
                            <td>{{ $usuarioRol->rol->nombre }}</td>
                            {{-- firstWhere: busca en la lista de centros el que tiene ese id. --}}
                            <td>{{ $usuarioRol->centro_id ? ($centros->firstWhere('id', $usuarioRol->centro_id)->nombre ?? '—') : 'Todos' }}</td>
                            <td>{{ $usuarioRol->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $usuarioRol->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($usuarioRol->estaVigente())
                                    <span class="badge badge-success">Vigente</span>
                                @elseif($usuarioRol->fecha_inicio->isFuture())
                                    <span class="badge badge-primary">Futuro</span>
                                @else
                                    <span class="badge badge-muted">Cerrado</span>
                                @endif
                            </td>
                            {{-- Cerrar: solo los que no tienen fecha de fin. --}}
                            <td class="actions">
                                @if($usuarioRol->fecha_fin === null && auth()->user()->can('usuarios.editar'))
                                    <form method="POST" action="{{ route('usuarios.roles.cerrar', [$usuario, $usuarioRol]) }}" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <input class="form-control" type="date" name="fecha_fin" value="{{ now()->format('Y-m-d') }}" style="width:auto; display:inline-block;">
                                        <button class="btn btn-danger btn-sm" type="submit">Cerrar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">Todavía no tiene ningún rol.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Formulario para añadir un rol (solo con permiso usuarios.editar). --}}
        @can('usuarios.editar')
        <form method="POST" action="{{ route('usuarios.roles.store', $usuario) }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="rol_id">Rol</label>
                    <select class="form-select" id="rol_id" name="rol_id">
                        <option value="">Elige…</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" @selected((string) old('rol_id') === (string) $rol->id)>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="centro_id">Centro</label>
                    <select class="form-select" id="centro_id" name="centro_id">
                        <option value="">Todos los centros</option>
                        @foreach($centros as $centro)
                            <option value="{{ $centro->id }}" @selected((string) old('centro_id') === (string) $centro->id)>{{ $centro->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_inicio">Desde</label>
                    <input class="form-control" id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_fin">Hasta (opcional)</label>
                    <input class="form-control" id="fecha_fin" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Añadir rol</button>
            </div>
        </form>
        @endcan
    </div>
</div>

@endif
@endsection
