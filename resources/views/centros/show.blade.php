@extends('plantillas.principal')

@section('title', 'Ficha del centro')
@section('topbar', 'Centros')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $centro->nombre }}</h1>
        <p>Información general y direcciones del centro.</p>
    </div>

    <a class="btn btn-secondary"
        href="{{ route('contactos.centros.index') }}">
        Volver
    </a>
</div>


<div class="card">
    <div class="card-body">
        <h2>Datos generales</h2>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nombre</label>
                <p>{{ $centro->nombre }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">Empresa</label>
                <p>
                    {{ $centro->empresa?->nombre_comercial
                        ?: $centro->empresa?->razon_social
                        ?: 'Sin empresa' }}
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Estado</label>

                @if($centro->activo)
                <span class="badge badge-success">Activo</span>
                @else
                <span class="badge badge-muted">Inactivo</span>
                @endif
            </div>
        </div>

        @can('centros.editar', $centro->id)
        <div class="form-actions">
            <a class="btn btn-primary"
                href="{{ route('contactos.centros.edit', $centro->id) }}">
                Editar centro
            </a>
        </div>
        @endcan
    </div>
</div>

<div class="page-heading">
    <div>
        <h2>Direcciones</h2>
        <p>Direcciones físicas asociadas a este centro.</p>
    </div>

    @can('centros.editar', $centro->id)
    <a class="btn btn-primary"
        href="{{ route('contactos.centros.direcciones.create', [
               'centroId' => $centro->id
           ]) }}">
        Añadir dirección
    </a>
    @endcan
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Dirección</th>
                    <th>Municipio</th>
                    <th>Código postal</th>
                    <th>Tipo</th>
                    <th>Principal</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($centro->direcciones as $relacion)
                <tr>
                    <td>
                        {{ $relacion->direccion?->linea_direccion_1 }}

                        @if($relacion->direccion?->linea_direccion_2)
                        <br>
                        {{ $relacion->direccion->linea_direccion_2 }}
                        @endif
                    </td>

                    <td>
                        {{ $relacion->direccion?->municipio?->nombre ?? '—' }}

                        @if($relacion->direccion?->municipio?->provincia)
                        ({{ $relacion->direccion->municipio->provincia->nombre }})
                        @endif
                    </td>

                    <td>
                        {{ $relacion->direccion?->codigo_postal ?: '—' }}
                    </td>

                    <td>{{ ucfirst($relacion->tipo) }}</td>

                    <td>
                        @if($relacion->principal)
                        <span class="badge badge-success">Sí</span>
                        @else
                        No
                        @endif
                    </td>

                    <td>
                        @if($relacion->activo)
                        <span class="badge badge-success">Activa</span>
                        @else
                        <span class="badge badge-muted">Inactiva</span>
                        @endif
                    </td>

                    <td>
                        @can('centros.editar', $centro->id)
                        <div class="form-actions">
                            <a class="btn btn-secondary"
                                href="{{ route('contactos.centros.direcciones.edit', [
                                           'centroId' => $centro->id,
                                           'relacionId' => $relacion->id
                                       ]) }}">
                                Editar
                            </a>

                            @if($relacion->activo && !$relacion->principal)
                            <form method="POST"
                                action="{{ route('contactos.centros.direcciones.principal', [
                                                  'centroId' => $centro->id,
                                                  'relacionId' => $relacion->id
                                              ]) }}">
                                @csrf

                                <button class="btn btn-secondary" type="submit">
                                    Hacer principal
                                </button>
                            </form>
                            @endif

                            <form method="POST"
                                action="{{ route('contactos.centros.direcciones.destroy', [
                                              'centroId' => $centro->id,
                                              'relacionId' => $relacion->id
                                          ]) }}"
                                onsubmit="return confirm('¿Quieres desvincular esta dirección del centro? La dirección no se eliminará de la base de datos.');">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-secondary" type="submit">
                                    Desvincular
                                </button>
                            </form>
                        </div>
                        @else
                        —
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        Este centro todavía no tiene direcciones asociadas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="page-heading">
    <div>
        <h2>Teléfonos</h2>
        <p>Teléfonos asociados a este centro.</p>
    </div>

    @can('centros.editar', $centro->id)
    <a class="btn btn-primary"
        href="{{ route('contactos.centros.telefonos.create', ['centroId' => $centro->id]) }}">
        Añadir teléfono
    </a>
    @endcan
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Teléfono</th>
                    <th>Extensión</th>
                    <th>Tipo</th>
                    <th>Principal</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($centro->telefonos as $relacion)
                <tr>
                    <td>
                        {{ $relacion->telefono?->prefijo_internacional }}
                        {{ $relacion->telefono?->numero }}
                    </td>

                    <td>
                        {{ $relacion->telefono?->extension ?: '—' }}
                    </td>

                    <td>{{ ucfirst($relacion->tipo) }}</td>

                    <td>
                        @if($relacion->principal)
                        <span class="badge badge-success">Sí</span>
                        @else
                        No
                        @endif
                    </td>

                    <td>
                        @if($relacion->activo)
                        <span class="badge badge-success">Activo</span>
                        @else
                        <span class="badge badge-muted">Inactivo</span>
                        @endif
                    </td>

                    <td>
                        @can('centros.editar', $centro->id)
                        <div class="form-actions">
                            <a class="btn btn-secondary"
                                href="{{ route('contactos.centros.telefonos.edit', [
                                           'centroId' => $centro->id,
                                           'relacionId' => $relacion->id
                                       ]) }}">
                                Editar
                            </a>

                            @if($relacion->activo && !$relacion->principal)
                            <form method="POST"
                                action="{{ route('contactos.centros.telefonos.principal', [
                                                  'centroId' => $centro->id,
                                                  'relacionId' => $relacion->id
                                              ]) }}">
                                @csrf

                                <button class="btn btn-secondary" type="submit">
                                    Hacer principal
                                </button>
                            </form>
                            @endif

                            <form method="POST"
                                action="{{ route('contactos.centros.telefonos.destroy', [
                                              'centroId' => $centro->id,
                                              'relacionId' => $relacion->id
                                          ]) }}"
                                onsubmit="return confirm('¿Quieres desvincular este teléfono del centro? El teléfono no se eliminará de la base de datos.');">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-secondary" type="submit">
                                    Desvincular
                                </button>
                            </form>
                        </div>
                        @else
                        —
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        Este centro todavía no tiene teléfonos asociados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<div class="page-heading">
    <div>
        <h2>Correos electrónicos</h2>
        <p>Correos electrónicos asociados a este centro.</p>
    </div>
    @can('centros.editar', $centro->id)
    <a class="btn btn-primary"
        href="{{ route('contactos.centros.correos.create', ['centroId' => $centro->id]) }}">
        Añadir correo
    </a>
    @endcan
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Correo electrónico</th>
                    <th>Tipo</th>
                    <th>Principal</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($centro->correos as $relacion)
                <tr>
                    <td>{{ $relacion->correoElectronico?->email }}</td>
                    <td>{{ ucfirst($relacion->tipo) }}</td>
                    <td>
                        @if($relacion->principal)
                        <span class="badge badge-success">Sí</span>
                        @else
                        No
                        @endif
                    </td>
                    <td>
                        @if($relacion->activo)
                        <span class="badge badge-success">Activo</span>
                        @else
                        <span class="badge badge-muted">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        @can('centros.editar', $centro->id)
                        <div class="form-actions">
                            <a class="btn btn-secondary"
                                href="{{ route('contactos.centros.correos.edit', ['centroId' => $centro->id, 'relacionId' => $relacion->id]) }}">
                                Editar
                            </a>

                            @if($relacion->activo && !$relacion->principal)
                            <form method="POST"
                                action="{{ route('contactos.centros.correos.principal', ['centroId' => $centro->id, 'relacionId' => $relacion->id]) }}">
                                @csrf
                                <button class="btn btn-secondary" type="submit">
                                    Hacer principal
                                </button>
                            </form>
                            @endif

                            <form method="POST"
                                action="{{ route('contactos.centros.correos.destroy', ['centroId' => $centro->id, 'relacionId' => $relacion->id]) }}"
                                onsubmit="return confirm('¿Quieres desvincular este correo electrónico del centro? El correo no se eliminará de la base de datos.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-secondary" type="submit">
                                    Desvincular
                                </button>
                            </form>
                        </div>
                        @else
                        —
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        Este centro todavía no tiene correos electrónicos asociados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection