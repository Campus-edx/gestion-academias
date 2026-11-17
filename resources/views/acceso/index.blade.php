{{-- Nombre y color de la aplicación (de la empresa activa o, si no hay, valores por defecto). --}}
@php
    $nombreAplicacion = $empresa?->nombre_aplicacion ?: config('app.name');
    $colorPrincipal = $empresa?->color_principal ?: '#0d6efd';
@endphp
<!doctype html>
<html lang="es">
{{-- Cabecera: título de la pestaña y estilos. --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso · {{ $nombreAplicacion }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>:root { --primary: {{ $colorPrincipal }}; }</style>
</head>
<body>
{{-- Página de login: dos columnas. --}}
<div class="login-page">
    {{-- Columna izquierda: logo, nombre y presentación. --}}
    <section class="login-hero">
        <div class="login-brand">
            <div class="brand-mark">{{ mb_strtoupper(mb_substr($nombreAplicacion, 0, 1)) }}</div>
            <strong>{{ $nombreAplicacion }}</strong>
        </div>
        <div>
            <h1>Gestión clara para empresas de formación.</h1>
            <p>Accede al entorno de gestión con tu cuenta de usuario.</p>
        </div>
        <small>Beta 0.2.1 · Entorno local de desarrollo</small>
    </section>

    {{-- Columna derecha: formulario de acceso. --}}
    <section class="login-panel">
        <div class="login-card">
            <h2>Iniciar sesión</h2>
            <p>Accede al entorno de administración.</p>

            {{-- Si el login falla, muestra el primer error. --}}
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            {{-- Formulario: se envía por POST a la ruta login.attempt (método acceder del AccesoController). --}}
            <form method="POST" action="{{ route('login.attempt') }}">
                {{-- Código de seguridad obligatorio en los formularios. --}}
                @csrf
                <div class="form-group">
                    <label class="form-label" for="correo_electronico">Email</label>
                    {{-- Campo email. old() mantiene lo escrito si hay error. --}}
                    <input class="form-control" id="correo_electronico" name="correo_electronico" type="email" value="{{ old('correo_electronico') }}" autocomplete="email" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contrasena">Contraseña</label>
                    {{-- Campo contraseña (type password = se ve con puntos). --}}
                    <input class="form-control" id="contrasena" name="contrasena" type="password" autocomplete="current-password" required>
                </div>
                <div class="form-group checkbox-row">
                    {{-- Casilla "Mantener la sesión iniciada". --}}
                    <input id="recordar" name="recordar" type="checkbox" value="1">
                    <label for="recordar">Mantener la sesión iniciada</label>
                </div>
                {{-- Botón que envía el formulario. --}}
                <button class="btn btn-primary btn-block" type="submit">Entrar</button>
            </form>

            {{-- Recuadro con el usuario de prueba. --}}
            <div style="margin-top:24px; padding:14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:9px; font-size:13px; color:#6b7280;">
                <strong style="color:#374151;">Usuario demo administrador</strong><br>
                admin@demo.local / Admin1234!
            </div>
        </div>
    </section>
</div>
</body>
</html>
