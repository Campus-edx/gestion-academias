
@extends('plantillas.principal')

@section('title', $modoEdicion ? 'Editar grupo' : 'Nuevo grupo')
@section('topbar', 'Grupos')

@section('content')

<div class="page-heading">
    <div>
        <h1>{{ $modoEdicion ? 'Editar grupo' : 'Nuevo grupo' }}</h1>
        <p>
            {{ $modoEdicion
                ? 'Modifica los datos del grupo académico.'
                : 'Registra un nuevo grupo académico.' }}
        </p>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <form method="POST"
            action="{{ $modoEdicion
                ? route('grupos.update', $grupo->id)
                : route('grupos.store') }}">

            @csrf

            @if($modoEdicion)
                @method('PUT')
            @endif

            <div class="form-grid">

                {{-- Temporada --}}
                <div class="form-group">
                    <label class="form-label" for="temporada_id">
                        Temporada
                    </label>

                    <select class="form-select"
                        id="temporada_id"
                        name="temporada_id"
                        required>

                        <option value="">Seleccionar temporada</option>

                        @foreach($temporadas as $temporada)
                            <option value="{{ $temporada->id }}"
                                @selected(old('temporada_id', $grupo->temporada_id ?? '') == $temporada->id)>
                                {{ $temporada->nombre }}
                                ({{ \Carbon\Carbon::parse($temporada->fecha_inicio)->format('d/m/Y') }}
                                - {{ \Carbon\Carbon::parse($temporada->fecha_fin)->format('d/m/Y') }})
                            </option>
                        @endforeach

                    </select>

                    @error('temporada_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Curso --}}
                <div class="form-group">
                    <label class="form-label" for="curso_id">
                        Curso
                    </label>

                    <select class="form-select"
                        id="curso_id"
                        name="curso_id"
                        required
                        disabled>

                        <option value="">Selecciona primero una temporada</option>

                    </select>

                    @error('curso_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Centro --}}
                <div class="form-group">
                    <label class="form-label" for="centro_id">
                        Centro
                    </label>

                    <select class="form-select"
                        id="centro_id"
                        name="centro_id"
                        required
                        disabled>

                        <option value="">Selecciona primero una temporada</option>

                    </select>

                    @error('centro_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Plataforma --}}
                <div class="form-group">
                    <label class="form-label" for="plataforma_id">
                        Plataforma
                    </label>

                    <select class="form-select"
                        id="plataforma_id"
                        name="plataforma_id"
                        disabled>

                        <option value="">Sin plataforma</option>

                    </select>

                    @error('plataforma_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nombre --}}
                <div class="form-group">
                    <label class="form-label" for="nombre">
                        Nombre del grupo
                    </label>

                    <input
                        class="form-control"
                        id="nombre"
                        name="nombre"
                        type="text"
                        maxlength="80"
                        value="{{ old('nombre', $grupo->nombre ?? '') }}"
                        required>

                    @error('nombre')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Capacidad --}}
                <div class="form-group">
                    <label class="form-label" for="capacidad_maxima">
                        Capacidad máxima
                    </label>

                    <input
                        class="form-control"
                        id="capacidad_maxima"
                        name="capacidad_maxima"
                        type="number"
                        min="1"
                        max="255"
                        value="{{ old('capacidad_maxima', $grupo->capacidad_maxima ?? '') }}"
                        required>

                    @error('capacidad_maxima')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Estado --}}
                <div class="form-group">
                    <label class="form-label" for="estado">
                        Estado
                    </label>

                    <select class="form-select"
                        id="estado"
                        name="estado"
                        required>

                        @foreach([
                            'planificado' => 'Planificado',
                            'abierto' => 'Abierto',
                            'en_curso' => 'En curso',
                            'finalizado' => 'Finalizado',
                            'cancelado' => 'Cancelado'
                        ] as $valor => $texto)

                            <option value="{{ $valor }}"
                                @selected(old('estado', $grupo->estado ?? 'planificado') == $valor)>
                                {{ $texto }}
                            </option>

                        @endforeach

                    </select>

                    @error('estado')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            {{-- Botones --}}
            <div class="form-actions">

                <a href="{{ route('grupos.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit"
                    id="guardarGrupo"
                    class="btn btn-primary"
                    disabled>
                    {{ $modoEdicion ? 'Guardar cambios' : 'Guardar grupo' }}
                </button>

            </div>

        </form>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const temporada = document.getElementById('temporada_id');
        const curso = document.getElementById('curso_id');
        const centro = document.getElementById('centro_id');
        const plataforma = document.getElementById('plataforma_id');
        const guardarGrupo = document.getElementById('guardarGrupo');

        const urlOpciones = @json(route('grupos.opciones', ['temporadaId' => '__TEMPORADA__']));

        // Valores que deben recuperarse al editar o tras un error de validación.
        const cursoSeleccionado = @json(old('curso_id', $grupo->curso_id ?? ''));
        const centroSeleccionado = @json(old('centro_id', $grupo->centro_id ?? ''));
        const plataformaSeleccionada = @json(old('plataforma_id', $grupo->plataforma_id ?? ''));

        // Limpiar un desplegable y mostrar un mensaje.
        function limpiarSelect(select, texto, deshabilitar = true) {
            select.innerHTML = '';
            select.add(new Option(texto, ''));
            select.disabled = deshabilitar;
        }

        // Cargar cursos, centros y plataformas de la temporada.
        async function cargarOpciones(restaurarValores = false) {

            guardarGrupo.disabled = true;

            limpiarSelect(curso, 'Cargando cursos...');
            limpiarSelect(centro, 'Cargando centros...');
            limpiarSelect(plataforma, 'Sin plataforma');

            if (!temporada.value) {
                limpiarSelect(curso, 'Selecciona primero una temporada');
                limpiarSelect(centro, 'Selecciona primero una temporada');
                return;
            }

            try {

                const respuesta = await fetch(
                    urlOpciones.replace('__TEMPORADA__', temporada.value)
                );

                if (!respuesta.ok) {
                    throw new Error('No se pudieron cargar las opciones.');
                }

                const datos = await respuesta.json();

                // Añadir opción inicial a cada desplegable.
                limpiarSelect(curso, 'Seleccionar curso', false);
                limpiarSelect(centro, 'Seleccionar centro', false);
                limpiarSelect(plataforma, 'Sin plataforma', false);

                // Cargar cursos.
                datos.cursos.forEach(item => {
                    curso.add(new Option(item.nombre, item.id));
                });

                // Cargar centros.
                datos.centros.forEach(item => {
                    centro.add(new Option(item.nombre, item.id));
                });

                // Cargar plataformas.
                datos.plataformas.forEach(item => {
                    plataforma.add(new Option(item.nombre, item.id));
                });

                // Recuperar los valores del grupo cuando estamos editando.
                if (restaurarValores) {
                    curso.value = cursoSeleccionado;
                    centro.value = centroSeleccionado;
                    plataforma.value = plataformaSeleccionada;
                }

                // Comprobar si hay cursos disponibles.
                if (datos.cursos.length === 0) {
                    limpiarSelect(curso, 'No hay cursos disponibles');
                }

                // Comprobar si hay centros disponibles.
                if (datos.centros.length === 0) {
                    limpiarSelect(centro, 'No hay centros disponibles');
                }

                // Habilitar Guardar solamente si existen cursos y centros.
                guardarGrupo.disabled =
                    datos.cursos.length === 0 || datos.centros.length === 0;

            } catch (error) {

                limpiarSelect(curso, 'Error al cargar cursos');
                limpiarSelect(centro, 'Error al cargar centros');
                limpiarSelect(plataforma, 'Sin plataforma');

                guardarGrupo.disabled = true;

                console.error(error);

                alert('No se pudieron cargar los datos de la temporada.');
            }
        }

        // Cuando cambia la temporada, cargar sus opciones.
        temporada.addEventListener('change', function() {
            cargarOpciones(false);
        });

        // Si ya hay una temporada seleccionada, cargar sus opciones.
        // Esto ocurre al abrir un grupo para editarlo.
        if (temporada.value) {
            cargarOpciones(true);
        }

    });
</script>

@endsection