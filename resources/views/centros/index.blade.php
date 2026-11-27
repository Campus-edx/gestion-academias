@extends('plantillas.principal')

@section('title', 'Centros')
@section('topbar', 'Centros')

@section('content')

<div class="page-heading">
    <div>
        <h1>Centros</h1>
        <p>Gestión de los centros asociados a las empresas.</p>
    </div>

    @if($puedeCrear)
    <a class="btn btn-primary"
        href="{{ route('contactos.centros.create') }}">
        Nuevo centro
    </a>
    @endif
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Empresa</th>
                    <th>Direcciones</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @forelse($centros as $centro)
                <tr>
                    <td>{{ $centro->nombre }}</td>

                    <td>
                        {{ $centro->empresa?->nombre_comercial
                                ?: $centro->empresa?->razon_social
                                ?: 'Sin empresa' }}
                    </td>

                    <td>
                        {{ $centro->direcciones->count() }}
                    </td>

                    <td>
                        @if($centro->activo)
                        <span class="badge badge-success">Activo</span>
                        @else
                        <span class="badge badge-muted">Inactivo</span>
                        @endif
                    </td>

                    <td class="actions">
                        <a class="btn btn-secondary btn-sm"
                            href="{{ route('contactos.centros.show', $centro->id) }}">
                            Ver
                        </a>

                        <a class="btn btn-secondary btn-sm"
                            href="{{ route('contactos.centros.grupos', $centro->id) }}">
                            Grupos
                        </a>

                        @can('centros.editar', $centro->id)
                        <a class="btn btn-secondary btn-sm"
                            href="{{ route('contactos.centros.edit', $centro->id) }}">
                            Editar
                        </a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        Todavía no hay centros registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection