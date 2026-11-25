
@extends('plantillas.principal')

@section('title', 'Ficha de temporada')
@section('topbar', 'Temporadas')

@section('content')

<div class="page-heading">
    <div>
        <h1>Ficha de temporada</h1>
        <p>Información general de la temporada.</p>
    </div>

    <div>
        <a href="{{ route('temporadas.edit', $temporada->id) }}" class="btn btn-primary">
            Editar
        </a>

        <a href="{{ route('temporadas.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <h3>Datos de la temporada</h3>

        <div class="form-grid">

            <div class="form-group">
                <label>Empresa</label>
                <p>{{ $temporada->empresa?->nombre_comercial ?: $temporada->empresa?->razon_social }}</p>
            </div>

            <div class="form-group">
                <label>Nombre</label>
                <p>{{ $temporada->nombre }}</p>
            </div>

            <div class="form-group">
                <label>Fecha de inicio</label>
                <p>{{ \Carbon\Carbon::parse($temporada->fecha_inicio)->format('d/m/Y') }}</p>
            </div>

            <div class="form-group">
                <label>Fecha de fin</label>
                <p>{{ \Carbon\Carbon::parse($temporada->fecha_fin)->format('d/m/Y') }}</p>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <p>{{ ucfirst($temporada->estado) }}</p>
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <p>{{ $temporada->observaciones ?: 'Sin observaciones' }}</p>
            </div>

        </div>

    </div>
</div>

<div class="card">
    <div class="card-body">

        <h3>Grupos asociados</h3>

        @if($temporada->grupos->isEmpty())
            <p>Esta temporada todavía no tiene grupos asociados.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Estado</th>
                            <th>Capacidad máxima</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($temporada->grupos as $grupo)
                            <tr>
                                <td>{{ $grupo->nombre }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $grupo->estado)) }}</td>
                                <td>{{ $grupo->capacidad_maxima }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>

@endsection