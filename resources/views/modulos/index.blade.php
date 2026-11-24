{{-- Listado de módulos con su materia, su nivel y la empresa que lo creó. --}}
@extends('plantillas.principal')

@section('title', 'Módulos')
@section('topbar', 'Módulos')

@section('content')

<div class="page-heading">
    <div>
        <h1>Módulos</h1>
        <p>Los contenidos con los que se montan los cursos. Los puede usar cualquier empresa.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('modulos.create') }}">Nuevo módulo</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Materia</th><th>Nivel</th><th>Creado por</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($modulos as $modulo)
                    <tr>
                        <td>{{ $modulo->nombre }}</td>
                        <td>{{ $modulo->materia->nombre }}</td>
                        {{-- ?-> evita el error si no tiene nivel; ?? '—' pone una raya en ese caso. --}}
                        <td>{{ $modulo->nivel?->nombre ?? '—' }}</td>
                        <td>{{ $modulo->empresa->nombre_comercial ?: $modulo->empresa->razon_social }}</td>
                        <td>
                            @if($modulo->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-muted">Inactivo</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-secondary btn-sm" href="{{ route('modulos.edit', $modulo) }}">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">Todavía no hay módulos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
