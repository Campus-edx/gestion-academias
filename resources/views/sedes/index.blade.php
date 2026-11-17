{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

{{-- Título de la pestaña y de la barra superior. --}}
@section('title', 'Sedes')
@section('topbar', 'Sedes')

{{-- Contenido de esta pantalla: --}}
@section('content')

{{-- Encabezado: título, explicación y botón para crear una sede nueva. --}}
<div class="page-heading">
    <div>
        <h1>Sedes</h1>
        <p>Centros de la academia y lugares externos donde se imparte.</p>
    </div>
    {{-- Enlace a la ruta sedes.create (formulario vacío). --}}
    <a class="btn btn-primary" href="{{ route('sedes.create') }}">Nueva sede</a>
</div>

{{-- Tarjeta con la tabla de sedes. --}}
<div class="card">
    <div class="table-wrap">
        <table>
            {{-- Fila de títulos de las columnas. La última va vacía (botones). --}}
            <thead>
                <tr><th>Nombre</th><th>Localidad</th><th>Tipo</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                {{-- Bucle: una fila por cada sede que envía el controlador.
                     @forelse es como @foreach, pero con un apartado @empty
                     por si la lista está vacía. --}}
                @forelse($sedes as $sede)
                    <tr>
                        <td>{{ $sede->nombre }}</td>
                        <td>{{ $sede->localidad }}</td>
                        {{-- ucfirst() pone la primera letra en mayúscula: propia → Propia. --}}
                        <td>{{ ucfirst($sede->tipo) }}</td>
                        {{-- Etiqueta verde si está activa; gris si no. --}}
                        <td>
                            @if($sede->activa)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-muted">Inactiva</span>
                            @endif
                        </td>
                        {{-- Botones de la fila. --}}
                        <td class="actions">
                            {{-- Editar: va a la ruta sedes.edit de esta sede (/sedes/3/editar). --}}
                            <a class="btn btn-secondary btn-sm" href="{{ route('sedes.edit', $sede) }}">Editar</a>
                            {{-- Borrar: es un formulario porque hay que enviar DELETE, no basta un enlace. --}}
                            <form method="POST" action="{{ route('sedes.destroy', $sede) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Borrar</button>
                            </form>
                        </td>
                    </tr>
                {{-- Si no hay ninguna sede, se muestra esta fila en su lugar. --}}
                @empty
                    <tr><td colspan="5" class="empty-state">Todavía no hay sedes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection