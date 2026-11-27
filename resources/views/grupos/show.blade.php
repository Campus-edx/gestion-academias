@extends('plantillas.principal')

@section('title', 'Ficha del grupo')
@section('topbar', 'Grupos')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $grupo->nombre }}</h1>
        <p>Ficha del grupo académico.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <div class="form-grid">

            <div class="form-group">
                <label class="form-label">Nombre del grupo</label>
                <p>{{ $grupo->nombre }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Temporada</label>
                <p>{{ $grupo->temporada_nombre }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Curso</label>
                <p>{{ $grupo->curso_nombre }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Centro</label>
                <p>{{ $grupo->centro_nombre }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Plataforma</label>
                <p>{{ $grupo->plataforma_nombre ?? 'Sin plataforma' }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Fecha de inicio</label>
                <p>{{ \Carbon\Carbon::parse($grupo->fecha_inicio)->format('d/m/Y') }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Fecha de fin</label>
                <p>{{ \Carbon\Carbon::parse($grupo->fecha_fin)->format('d/m/Y') }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Capacidad máxima</label>
                <p>{{ $grupo->capacidad_maxima }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Estado</label>
                <p>{{ ucfirst(str_replace('_', ' ', $grupo->estado)) }}</p>
            </div>

        </div>

        <div class="form-actions">

            <a href="{{ route('grupos.index') }}" class="btn btn-secondary">
                Volver al listado
            </a>

            @if($puedeEditar)
                <a href="{{ route('grupos.edit', $grupo->id) }}" class="btn btn-primary">
                    Editar grupo
                </a>
            @endif

        </div>

    </div>
</div>

@endsection
