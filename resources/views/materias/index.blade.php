{{-- Listado de materias. Copia del de categorías cambiando los nombres. --}}
@extends('plantillas.principal')

@section('title', 'Materias')
@section('topbar', 'Materias')

@section('content')

<div class="page-heading">
    <div>
        <h1>Materias</h1>
        <p>Inglés, Matemáticas, Lengua... Cada módulo es de una materia.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('materias.create') }}">Nueva materia</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($materias as $materia)
                    <tr>
                        <td>{{ $materia->nombre }}</td>
                        <td>
                            @if($materia->activo)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-muted">Inactiva</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('materias.edit', $materia) }}">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-state">Todavía no hay materias. Crea al menos una para poder dar de alta módulos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
