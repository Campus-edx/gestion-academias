@extends('plantillas.principal')

@section('title', 'Grupos')
@section('topbar', 'Grupos')

@section('content')

<div class="page-heading">
    <div>
        <h1>Grupos</h1>
        <p>Consulta y gestiona los grupos académicos.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">

        {{-- Buscador y botón de nuevo grupo --}}
        <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:20px; flex-wrap:wrap;">

            <input
                type="search"
                id="buscarGrupo"
                class="form-control"
                placeholder="Buscar..."
                style="max-width:300px;">

            <a href="{{ route('grupos.create') }}"
                class="btn btn-primary"
                style="background:#2563eb; color:#ffffff; padding:10px 18px; text-decoration:none;">
                + Nuevo grupo
            </a>

        </div>

        {{-- Tabla de grupos --}}
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Curso</th>
                        <th>Centro</th>
                        <th>Temporada</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Plazas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tablaGrupos">

                    @forelse($grupos as $grupo)
                    <tr>
                        <td>{{ $grupo->nombre }}</td>
                        <td>{{ $grupo->curso_nombre }}</td>
                        <td>{{ $grupo->centro_nombre }}</td>
                        <td>{{ $grupo->temporada_nombre }}</td>

                        <td>{{ \Carbon\Carbon::parse($grupo->fecha_inicio)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($grupo->fecha_fin)->format('d/m/Y') }}</td>

                        <td>{{ $grupo->capacidad_maxima }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $grupo->estado)) }}</td>

                        <td>
                            <span style="color:#777;">Pendiente</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:25px;">
                            No hay grupos registrados.
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Buscador en tiempo real --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const buscador = document.getElementById('buscarGrupo');
        const filas = document.querySelectorAll('#tablaGrupos tr');

        buscador.addEventListener('input', function() {
            const texto = this.value.toLowerCase();

            filas.forEach(fila => {
                fila.style.display = fila.textContent.toLowerCase().includes(texto) ?
                    '' :
                    'none';
            });
        });

    });
</script>

@endsection