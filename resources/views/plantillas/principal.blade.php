{{-- Datos que usa toda la plantilla: usuario conectado, su empresa, nombre y color de la aplicación --}}
@php
    $usuarioAutenticado = auth()->user();
    $empresa = $usuarioAutenticado->empresa;
    $nombreAplicacion = $empresa?->nombre_aplicacion ?: config('app.name');
    $colorPrincipal = $empresa?->color_principal ?: '#0d6efd';
@endphp
<!doctype html>
<html lang="es">
{{-- Cabecera: datos que no se ven (título de la pestaña, estilos, código de seguridad) --}}

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Título de la pestaña: el de cada pantalla + nombre de la aplicación --}}
    <title>@yield('title', 'Panel') · {{ $nombreAplicacion }}</title>
    {{-- Hoja de estilos del proyecto (public/css/app.css) --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Color principal de la empresa --}}
    <style>
        :root {
            --primary: {{ $colorPrincipal }};
        }
    </style>
</head>

<body>
    <div class="app-shell">

        {{-- MENÚ LATERAL --}}
        <aside class="sidebar">

            {{-- Logo (primera letra) y nombre de la aplicación --}}
            <div class="sidebar-brand">
                <div class="brand-mark">{{ mb_strtoupper(mb_substr($nombreAplicacion, 0, 1)) }}</div>
                <div>
                    <strong>{{ $nombreAplicacion }}</strong>
                    <small>Gestión de formación</small>
                </div>
            </div>

                        {{-- Enlaces del menú: Inicio y Configuración  --}}
            <div class="nav-label">Principal</div>
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <span class="nav-icon">⌂</span> Inicio
            </a>

            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                href="{{ route('settings.profile') }}">
                <span class="nav-icon">⚙</span> Configuración
            </a>
            
            {{-- NUEVO: sección de Alumnos y tutores del menú. --}}
            <div class="nav-label">Alumnos y tutores</div>
            {{-- Enlace al listado de alumnos. 'alumnos.*' lo marca en todas
            sus pantallas. --}}
            <a class="nav-link {{ request()->routeIs('alumnos.*') ? 'active'
            : '' }}" href="{{ route('alumnos.index') }}">
            <span class="nav-icon">☺</span> Alumnos
            </a>

            

            {{-- Parte de abajo del menú: datos del usuario conectado y botón de cerrar sesión --}}
            <div class="sidebar-bottom">
                {{-- Nombre y correo del usuario --}}
                <div class="user-mini">
                    <strong>{{ $usuarioAutenticado->nombre }}</strong>
                    <small>{{ $usuarioAutenticado->correo_electronico }}</small>
                </div>
                {{-- Botón de cerrar sesión (formulario POST a la ruta logout, con @csrf) --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-link logout-button" type="submit">
                        <span class="nav-icon">↪</span> Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        {{-- ZONA PRINCIPAL (a la derecha del menú) --}}
        <main class="main">

            {{-- Barra superior: título de la pantalla y nombre de la empresa --}}
            <header class="topbar">
                <div class="topbar-title">@yield('topbar', 'Inicio')</div>
                @if ($empresa)
                    <div class="topbar-company">{{ $empresa->nombre_comercial ?: $empresa->razon_social }}</div>
                @endif
            </header>

            {{-- Contenido de cada pantalla --}}
            <div class="content">
                {{-- Mensajes de éxito o de error (parciales/alertas.blade.php) --}}
                @include('parciales.alertas')
                {{-- Aquí se mete lo que cada vista pone en @section('content') --}}
                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>
