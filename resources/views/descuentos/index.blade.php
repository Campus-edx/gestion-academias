{{-- Descuentos de la empresa. --}}
@extends('plantillas.principal')

@section('title', 'Descuentos')
@section('topbar', 'Descuentos')

@section('content')

<div class="page-heading">
    <div>
        <h1>Descuentos</h1>
        <p>Los descuentos de tu empresa que se pueden elegir al matricular. No se borran: se desactivan.</p>
    </div>
    @can('facturas.emitir')
        <a class="btn btn-primary" href="{{ route('descuentos.create') }}">Nuevo descuento</a>
    @endcan
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Descuento</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($descuentos as $descuento)
                    <tr>
                        <td>{{ $descuento->nombre }}</td>
                        {{-- valorLegible(): "10 %" o "15,00 €" (función del modelo). --}}
                        <td>{{ $descuento->valorLegible() }}</td>
                        <td>
                            @if($descuento->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-muted">Desactivado</span>
                            @endif
                        </td>
                        <td class="actions">
                            @can('facturas.emitir')
                                <a class="btn btn-secondary btn-sm" href="{{ route('descuentos.edit', $descuento) }}">Editar</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">Todavía no hay descuentos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection