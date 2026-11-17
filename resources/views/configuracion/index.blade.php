{{-- Usa la plantilla común. --}}
@extends('plantillas.principal')

{{-- Título de la pestaña y de la barra superior. --}}
@section('title', 'Configuración')
@section('topbar', 'Configuración')

{{-- Contenido de esta pantalla: --}}
@section('content')
{{-- Encabezado. --}}
<div class="page-heading">
    <div>
        <h1>Configuración</h1>
        <p>Gestiona los datos de tu cuenta y tu contraseña.</p>
    </div>
</div>

{{-- Dos tarjetas: datos de usuario y cambiar contraseña. --}}
<div class="settings-grid">
    {{-- TARJETA 1: datos de usuario. --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Datos de usuario</h2>
                <p class="card-subtitle">Información asociada a tu cuenta.</p>
            </div>
        </div>

        <div class="card-body">
            {{-- Formulario: se envía a la ruta settings.profile.update (método actualizar). --}}
            <form method="POST" action="{{ route('settings.profile.update') }}">
                {{-- Código de seguridad. --}}
                @csrf
                {{-- Convierte el envío en PUT (modificar), como pide la ruta. --}}
                @method('PUT')

                <div class="form-grid">
                    {{-- Campo nombre. old() recuerda lo escrito; si no, pone el nombre actual. --}}
                    <div class="form-group full">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input
                            class="form-control"
                            id="nombre"
                            name="nombre"
                            type="text"
                            value="{{ old('nombre', $usuario->nombre) }}"
                            autocomplete="name"
                            required
                        >
                        {{-- Error del campo nombre, si lo hay. --}}
                        @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    {{-- Campo correo electrónico. --}}
                    <div class="form-group full">
                        <label class="form-label" for="correo_electronico">Email</label>
                        <input
                            class="form-control"
                            id="correo_electronico"
                            name="correo_electronico"
                            type="email"
                            value="{{ old('correo_electronico', $usuario->correo_electronico) }}"
                            autocomplete="email"
                            required
                        >
                        @error('correo_electronico')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-actions">
                    {{-- Botón que envía el formulario. --}}
                    <button class="btn btn-primary" type="submit">Guardar datos</button>
                </div>
            </form>
        </div>
    </div>

    {{-- TARJETA 2: cambiar contraseña. --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Cambiar contraseña</h2>
                <p class="card-subtitle">Por seguridad, necesitamos comprobar tu contraseña actual.</p>
            </div>
        </div>

        <div class="card-body">
            {{-- Formulario: se envía a la ruta settings.password.update (método actualizarContrasena). --}}
            <form method="POST" action="{{ route('settings.password.update') }}">
                {{-- Código de seguridad. --}}
                @csrf
                {{-- Envío como PUT. --}}
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        {{-- Contraseña actual. --}}
                        <label class="form-label" for="contrasena_actual">Contraseña actual</label>
                        <input
                            class="form-control"
                            id="contrasena_actual"
                            name="contrasena_actual"
                            type="password"
                            autocomplete="current-password"
                            required
                        >
                        @error('contrasena_actual')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group full">
                        {{-- Nueva contraseña. --}}
                        <label class="form-label" for="contrasena">Nueva contraseña</label>
                        <input
                            class="form-control"
                            id="contrasena"
                            name="contrasena"
                            type="password"
                            autocomplete="new-password"
                            required
                        >
                        <div class="form-help">Mínimo 8 caracteres.</div>
                        @error('contrasena')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group full">
                        {{-- Repetir la nueva. Se llama _confirmation para que funcione la regla confirmed. --}}
                        <label class="form-label" for="contrasena_confirmation">Repetir nueva contraseña</label>
                        <input
                            class="form-control"
                            id="contrasena_confirmation"
                            name="contrasena_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                        >
                    </div>
                </div>

                <div class="form-actions">
                    {{-- Botón que envía el formulario. --}}
                    <button class="btn btn-primary" type="submit">Cambiar contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
