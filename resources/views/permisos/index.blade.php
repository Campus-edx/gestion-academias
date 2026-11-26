{{-- Matriz de permisos: qué puede hacer cada rol (SCRUM-187). --}}
@extends('plantillas.principal')

@section('title', 'Permisos')
@section('topbar', 'Permisos')

@section('content')

<div class="page-heading">
    <div>
        <h1>Permisos de cada rol</h1>
        <p>Marca qué puede hacer cada rol. Los cambios valen para todos los usuarios con ese rol, en todas las empresas.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('permisos.update') }}">
            @csrf
            @method('PUT')

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Permiso</th>
                            {{-- Una columna por rol. --}}
                            @foreach($roles as $rol)
                                <th title="{{ $rol->descripcion }}">{{ $rol->nombre }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Un bloque por módulo: una fila con el nombre del módulo y debajo sus permisos. --}}
                        @foreach($permisosPorModulo as $modulo => $permisos)
                            <tr>
                                {{-- colspan: la celda ocupa todas las columnas (1 + número de roles). --}}
                                <td colspan="{{ $roles->count() + 1 }}"><strong>{{ ucfirst($modulo) }}</strong></td>
                            </tr>
                            @foreach($permisos as $permiso)
                                <tr>
                                    <td>
                                        {{ $permiso->nombre }}
                                        <div class="form-help">{{ $permiso->codigo }}</div>
                                    </td>
                                    @foreach($roles as $rol)
                                        <td>
                                            {{-- name="permisos[3][]": al enviar, llega una lista por rol con los permisos marcados.
                                                 aria-label: para que un lector de pantalla sepa qué casilla es. --}}
                                            <input type="checkbox" name="permisos[{{ $rol->id }}][]" value="{{ $permiso->id }}"
                                                   aria-label="{{ $rol->nombre }}: {{ $permiso->nombre }}"
                                                   @checked(in_array($rol->id . '-' . $permiso->id, $marcados, true))>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Guardar permisos</button>
            </div>
        </form>
    </div>
</div>
@endsection