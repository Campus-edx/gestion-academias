{{-- Listado de tarifas de los cursos de la empresa (SCRUM-354). --}}
@extends('plantillas.principal')

@section('title', 'Tarifas')
@section('topbar', 'Tarifas')

@section('content')

<div class="page-heading">
    <div>
        <h1>Tarifas</h1>
        <p>Precio de cada curso completo o de cada módulo suelto. Para cambiar un precio, cierra la tarifa y crea otra.</p>
    </div>
    @can('facturas.emitir')
        <a class="btn btn-primary" href="{{ route('tarifas.create') }}">Nueva tarifa</a>
    @endcan
</div>

{{-- Filtros. Formulario GET, sin @csrf. --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('tarifas.index') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label" for="curso_id">Curso</label>
                <select class="form-select" id="curso_id" name="curso_id">
                    <option value="">Todos</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" @selected((string) $cursoId === (string) $curso->id)>{{ $curso->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="estado">Mostrar</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="vigentes" @selected($estado === 'vigentes')>Vigentes hoy</option>
                    <option value="todas" @selected($estado === 'todas')>Todas (también cerradas y futuras)</option>
                </select>
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" type="submit">Filtrar</button>
            </div>
        </form>
    </div>
</div>

{{-- Los errores al cerrar una tarifa los enseña ya la plantilla común (parciales/alertas). --}}

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Curso</th><th>De</th><th>Tarifa</th><th>Importe</th><th>IVA</th><th>Con IVA</th><th>Periodicidad</th><th>Desde</th><th>Hasta</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($tarifas as $tarifa)
                    <tr>
                        {{-- curso y modulo los trae el controlador con sus joins. --}}
                        <td>{{ $tarifa->curso }}</td>
                        <td>{{ $tarifa->modulo ? 'Módulo ' . $tarifa->modulo : 'Curso completo' }}</td>
                        <td>{{ $tarifa->nombre }}</td>
                        {{-- number_format(número, decimales, separador decimal, separador de miles): 1234.5 → 1.234,50 --}}
                        <td>{{ number_format((float) $tarifa->importe, 2, ',', '.') }} €</td>
                        <td>{{ number_format((float) $tarifa->iva_porcentaje, 2, ',', '.') }} %</td>
                        <td>{{ number_format($tarifa->importeConIva(), 2, ',', '.') }} €</td>
                        <td>{{ \App\Models\Tarifas\Tarifas::PERIODICIDADES[$tarifa->periodicidad] ?? $tarifa->periodicidad }}</td>
                        <td>{{ $tarifa->vigente_desde->format('d/m/Y') }}</td>
                        <td>{{ $tarifa->vigente_hasta?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @if($tarifa->estaVigente())
                                <span class="badge badge-success">Vigente</span>
                            @elseif($tarifa->vigente_desde->isFuture())
                                <span class="badge badge-primary">Futura</span>
                            @else
                                <span class="badge badge-muted">Cerrada</span>
                            @endif
                        </td>
                        {{-- Cerrar: solo las que no tienen fecha "hasta", y solo con permiso. --}}
                        <td class="actions">
                            @if($tarifa->vigente_hasta === null)
                                @can('facturas.emitir')
                                    <form method="POST" action="{{ route('tarifas.cerrar', $tarifa) }}" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <input class="form-control" type="date" name="vigente_hasta" value="{{ now()->format('Y-m-d') }}" style="width:auto; display:inline-block;" aria-label="Válida hasta">
                                        <button class="btn btn-danger btn-sm" type="submit">Cerrar</button>
                                    </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="empty-state">No hay tarifas con estos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
