{{-- Listado de categorías de curso. Es el listado más sencillo: nombre y estado. --}}
@extends('plantillas.principal')

@section('title', 'Categorías de curso')
@section('topbar', 'Categorías de curso')

@section('content')

<div class="page-heading">
    <div>
        <h1>Categorías de curso</h1>
        <p>Para agrupar los cursos: Idiomas, Refuerzo escolar, Informática...</p>
    </div>
    <a class="btn btn-primary" href="{{ route('categorias_curso.create') }}">Nueva categoría</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($categorias as $categoria)
                    <tr>
                        <td>{{ $categoria->nombre }}</td>
                        <td>
                            @if($categoria->activo)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-muted">Inactiva</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('categorias_curso.edit', $categoria) }}">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-state">Todavía no hay categorías. Crea al menos una para poder dar de alta cursos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
