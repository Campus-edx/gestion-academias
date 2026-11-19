{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

{{-- Título de la pestaña y de la barra superior. --}}
@section('title', 'Alumnos')
@section('topbar', 'Alumnos')

{{-- Contenido de esta pantalla: --}}
@section('content')

{{-- Encabezado: título, explicación y botón para dar de alta un alumno. --}}
<div class="page-heading">
    <div>
        <h1>Alumnos</h1>
        <p>Alumnos de la academia. Un alumno es el mismo aunque esté en varias empresas.</p>
    </div>
    {{-- Enlace a la ruta alumnos.create (formulario vacío). --}}
    <a class="btn btn-primary" href="{{ route('alumnos.create') }}">Nuevo alumno</a>
</div>

{{-- Buscador. Es un formulario GET: lo escrito viaja en la dirección (/alumnos?buscar=garcia).
     No lleva @csrf porque no modifica nada, solo pide el listado filtrado. --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('alumnos.index') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label" for="buscar">Buscar por nombre, apellidos o documento</label>
                {{-- value: se deja escrito lo que se buscó, para que se vea. --}}
                <input class="form-control" id="buscar" name="buscar" type="text" value="{{ $buscar }}">
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" type="submit">Buscar</button>
                {{-- Si hay una búsqueda, un enlace para volver al listado completo. --}}
                @if($buscar !== '')
                    <a class="btn btn-secondary" href="{{ route('alumnos.index') }}">Ver todos</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tarjeta con la tabla de alumnos. --}}
<div class="card">
    <div class="table-wrap">
        <table>
            {{-- Fila de títulos de las columnas. La última va vacía (botones). --}}
            <thead>
                <tr><th>Apellidos y nombre</th><th>Documento</th><th>Fecha de nacimiento</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                {{-- Una fila por cada alumno. $alumno->persona trae sus datos personales. --}}
                @forelse($alumnos as $alumno)
                    <tr>
                        <td>{{ $alumno->persona->apellidos }}, {{ $alumno->persona->nombre }}</td>
                        {{-- Si no tiene documento, se muestra una raya. strtoupper: dni → DNI. --}}
                        <td>
                            @if($alumno->persona->numero_documento)
                                {{ strtoupper($alumno->persona->tipo_documento) }} {{ $alumno->persona->numero_documento }}
                            @else
                                —
                            @endif
                        </td>
                        {{-- format('d/m/Y'): la fecha a la española (05/03/2012). --}}
                        <td>{{ $alumno->persona->fecha_nacimiento?->format('d/m/Y') }}</td>
                        {{-- Etiqueta verde si está activo; gris si está de baja. --}}
                        <td>
                            @if($alumno->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-muted">De baja</span>
                            @endif
                        </td>
                        {{-- Botón para ver y modificar la ficha (no hay botón de borrar: se da de baja). --}}
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('alumnos.edit', $alumno) }}">Ver / editar</a>
                        </td>
                    </tr>
                {{-- Si no hay alumnos (o la búsqueda no encuentra nada). --}}
                @empty
                    <tr><td colspan="5" class="empty-state">
                        {{ $buscar !== '' ? 'Ningún alumno coincide con la búsqueda.' : 'Todavía no hay alumnos.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
