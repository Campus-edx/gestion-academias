@extends('plantillas.principal')

@section('title', 'Temporadas')

@section('topbar', 'Temporadas')

@section('content')

<div class="page-heading">
    <div>
        <h1>Temporadas</h1>
        <p>Consulta y administra las temporadas registradas.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 20px;">

            <form action="{{ route('temporadas.index') }}" method="GET">
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar temporada o empresa..."
                    value="{{ $buscar }}">
            </form>

            @if ($puedeCrear)
                <a href="{{ route('temporadas.create') }}" class="btn btn-primary">
                    + Nueva temporada
                </a>
            @endif

        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Temporada</th>
                        <th>Empresa</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($temporadas as $temporada)
                    <tr>
                        <td>{{ $temporada->nombre }}</td>

                        <td>
                            {{ $temporada->empresa?->nombre_comercial ?: $temporada->empresa?->razon_social }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($temporada->fecha_inicio)->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($temporada->fecha_fin)->format('d/m/Y') }}
                        </td>

                        <td>{{ ucfirst($temporada->estado) }}</td>

                        <td>
                            <a href="{{ route('temporadas.show', $temporada->id) }}" class="btn btn-secondary">
                                Ver
                            </a>

                            @if ($temporadasEditables[$temporada->id] ?? false)
                                <a href="{{ route('temporadas.edit', $temporada->id) }}" class="btn btn-secondary">
                                    Editar
                                </a>

                                <form action="{{ route('temporadas.destroy', $temporada->id) }}"
                                    method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-secondary">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            No hay temporadas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $temporadas->links() }}

    </div>
</div>

@endsection