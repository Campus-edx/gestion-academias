{{-- Ficha del curso. Sirve para crear y para editar. Al editar aparecen dos tarjetas más:
     los módulos del curso y el equipamiento que necesita su aula. --}}
@extends('plantillas.principal')

@section('title', $curso->exists ? 'Ficha del curso' : 'Nuevo curso')
@section('topbar', 'Cursos')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $curso->exists ? $curso->nombre : 'Nuevo curso' }}</h1>
        @if($curso->exists)
            <p>Duración total: {{ $horasTotales }} horas (suma de sus módulos).</p>
        @endif
    </div>
    <a class="btn btn-secondary" href="{{ route('cursos.index') }}">Volver al listado</a>
</div>

{{-- ═══════════ 1. DATOS DEL CURSO ═══════════ --}}
<div class="card">
    <div class="card-body">
        <h2>Datos del curso</h2>

        <form method="POST" action="{{ $curso->exists ? route('cursos.update', $curso) : route('cursos.store') }}">
            @csrf
            @if($curso->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" type="text" value="{{ old('nombre', $curso->nombre) }}">
                    @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="categoria_curso_id">Categoría</label>
                    <select class="form-select" id="categoria_curso_id" name="categoria_curso_id">
                        <option value="">Elige…</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected((string) old('categoria_curso_id', $curso->categoria_curso_id) === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_curso_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="modalidad">Modalidad</label>
                    <select class="form-select" id="modalidad" name="modalidad">
                        @foreach(['presencial' => 'Presencial', 'online' => 'Online', 'mixta' => 'Mixta'] as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('modalidad', $curso->modalidad) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    @error('modalidad')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Tipo de aula que necesita. Vacío = cualquiera. Si el curso es online, se ignora. --}}
                <div class="form-group">
                    <label class="form-label" for="tipo_espacio_id">Tipo de aula que necesita</label>
                    <select class="form-select" id="tipo_espacio_id" name="tipo_espacio_id">
                        <option value="">Cualquiera</option>
                        @foreach($tiposEspacio as $tipo)
                            <option value="{{ $tipo->id }}" @selected((string) old('tipo_espacio_id', $curso->tipo_espacio_id) === (string) $tipo->id)>{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                    @error('tipo_espacio_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Números: type="number" con mínimo y máximo. --}}
                @foreach([
                    'min_alumnos' => 'Mínimo de alumnos para abrir un grupo',
                    'max_alumnos' => 'Máximo de alumnos por grupo',
                    'edad_minima' => 'Edad mínima (opcional)',
                    'edad_maxima' => 'Edad máxima (opcional)',
                ] as $campo => $etiqueta)
                    <div class="form-group">
                        <label class="form-label" for="{{ $campo }}">{{ $etiqueta }}</label>
                        <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="number" min="0" max="255"
                               value="{{ old($campo, $curso->$campo) }}">
                        @error($campo)<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach

                <div class="form-group full">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $curso->descripcion) }}</textarea>
                    @error('descripcion')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Desmarcar = el curso deja de ofrecerse (no se borra). --}}
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $curso->activo))>
                        Activo
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('cursos.index') }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar datos</button>
            </div>
        </form>
    </div>
</div>

@if($curso->exists)

{{-- ═══════════ 2. MÓDULOS DEL CURSO ═══════════ --}}
<div class="card" id="modulos">
    <div class="card-body">
        <h2>Módulos del curso</h2>

        {{-- Errores de esta tarjeta (saco "modulo"). --}}
        @if($errors->modulo->any())
            <div class="alert alert-danger">
                @foreach($errors->modulo->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Orden</th><th>Módulo</th><th>Horas</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($modulosDelCurso as $cursoModulo)
                        <tr>
                            <td>{{ $cursoModulo->orden }}</td>
                            <td>{{ $cursoModulo->modulo->nombre }}</td>
                            <td>{{ $cursoModulo->horas }} h</td>
                            <td class="actions">
                                <form method="POST" action="{{ route('cursos.modulos.destroy', [$curso, $cursoModulo]) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">Este curso todavía no tiene módulos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('cursos.modulos.store', $curso) }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="modulo_id">Módulo</label>
                    <select class="form-select" id="modulo_id" name="modulo_id">
                        <option value="">Elige…</option>
                        @foreach($modulos as $modulo)
                            <option value="{{ $modulo->id }}" @selected((string) old('modulo_id') === (string) $modulo->id)>{{ $modulo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Orden propuesto: el siguiente al último. --}}
                <div class="form-group">
                    <label class="form-label" for="orden">Orden</label>
                    <input class="form-control" id="orden" name="orden" type="number" min="1" max="255"
                           value="{{ old('orden', $modulosDelCurso->count() + 1) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="horas">Horas</label>
                    <input class="form-control" id="horas" name="horas" type="number" min="1" value="{{ old('horas') }}">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Añadir módulo</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════ 3. EQUIPAMIENTO QUE NECESITA SU AULA ═══════════ --}}
<div class="card" id="equipamiento">
    <div class="card-body">
        <h2>Equipamiento que necesita el aula</h2>
        <p class="form-help">Lo usa la búsqueda de aulas: solo se proponen aulas que lo tengan.</p>

        @if($errors->equipamiento->any())
            <div class="alert alert-danger">
                @foreach($errors->equipamiento->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Equipamiento</th><th>Cantidad</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($equipamientoDelCurso as $fila)
                        <tr>
                            <td>{{ $fila->nombre }}</td>
                            <td>{{ $fila->cantidad }}</td>
                            <td class="actions">
                                {{-- $fila->id es el id de curso_equipamiento. --}}
                                <form method="POST" action="{{ route('cursos.equipamiento.destroy', [$curso, $fila->id]) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">No necesita nada especial.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('cursos.equipamiento.store', $curso) }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="equipamiento_id">Equipamiento</label>
                    <select class="form-select" id="equipamiento_id" name="equipamiento_id">
                        <option value="">Elige…</option>
                        @foreach($equipamientos as $equipamiento)
                            <option value="{{ $equipamiento->id }}" @selected((string) old('equipamiento_id') === (string) $equipamiento->id)>{{ $equipamiento->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="cantidad">Cantidad</label>
                    <input class="form-control" id="cantidad" name="cantidad" type="number" min="1" value="{{ old('cantidad', 1) }}">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Añadir equipamiento</button>
            </div>
        </form>
    </div>
</div>

@endif
@endsection
