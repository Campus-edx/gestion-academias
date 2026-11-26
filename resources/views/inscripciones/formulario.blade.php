{{-- Formulario para matricular a un alumno (SCRUM-273 y 220). Se abre desde la ficha del alumno. --}}
@extends('plantillas.principal')

@section('title', 'Matricular')
@section('topbar', 'Inscripciones')

@section('content')

<div class="page-heading">
    <div>
        <h1>Matricular a {{ $alumno->persona->nombreCompleto() }}</h1>
        <p>Solo salen los grupos de tu empresa que admiten alumnos, con sus plazas libres.</p>
    </div>
    <a class="btn btn-secondary" href="{{ route('alumnos.edit', $alumno) }}">Volver a la ficha</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('inscripciones.store') }}">
            @csrf
            {{-- El alumno no se elige: es el de la ficha de la que venimos. Va oculto. --}}
            <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">

            <div class="form-grid">
                {{-- Grupo: "Curso · Grupo (centro) — fechas — plazas libres". Los llenos salen desactivados. --}}
                <div class="form-group full">
                    <label class="form-label" for="grupo_id">Grupo</label>
                    <select class="form-select" id="grupo_id" name="grupo_id">
                        <option value="">Elige…</option>
                        @foreach($grupos as $grupo)
                            {{-- @disabled: no se puede elegir si no quedan plazas. --}}
                            <option value="{{ $grupo->id }}" @selected((string) old('grupo_id') === (string) $grupo->id) @disabled($grupo->plazas_libres <= 0)>
                                {{ $grupo->curso }} · {{ $grupo->grupo }} ({{ $grupo->centro }})
                                — del {{ \Carbon\Carbon::parse($grupo->fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($grupo->fecha_fin)->format('d/m/Y') }}
                                — {{ $grupo->plazas_libres }} {{ $grupo->plazas_libres == 1 ? 'plaza libre' : 'plazas libres' }}
                            </option>
                        @endforeach
                    </select>
                    @if($grupos->isEmpty())
                        <p class="form-help">Todavía no hay grupos abiertos. Los crea la Persona 3.</p>
                    @endif
                    @error('grupo_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- SCRUM-220: quién paga. Solo el propio alumno o uno de sus tutores. --}}
                <div class="form-group">
                    <label class="form-label" for="pagador_persona_id">Quién paga</label>
                    <select class="form-select" id="pagador_persona_id" name="pagador_persona_id">
                        @foreach($pagadores as $pagador)
                            <option value="{{ $pagador->id }}" @selected((string) old('pagador_persona_id') === (string) $pagador->id)>
                                {{ $pagador->nombre }} {{ $pagador->apellidos }} ({{ $pagador->relacion }})
                            </option>
                        @endforeach
                    </select>
                    @error('pagador_persona_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Tarifa del curso (SCRUM-358), de momento opcional. <optgroup> agrupa las opciones por curso;
                     el controlador comprueba que la elegida es del curso del grupo. --}}
                <div class="form-group">
                    <label class="form-label" for="tarifa_id">Tarifa (opcional de momento)</label>
                    <select class="form-select" id="tarifa_id" name="tarifa_id">
                        <option value="">Sin tarifa</option>
                        {{-- groupBy('curso'): una lista por curso. --}}
                        @foreach($tarifas->groupBy('curso') as $curso => $tarifasDelCurso)
                            <optgroup label="{{ $curso }}">
                                @foreach($tarifasDelCurso as $tarifa)
                                    <option value="{{ $tarifa->id }}" @selected((string) old('tarifa_id') === (string) $tarifa->id)>
                                        {{ $tarifa->nombre }} — {{ number_format((float) $tarifa->importe, 2, ',', '.') }} € + {{ rtrim(rtrim(number_format((float) $tarifa->iva_porcentaje, 2, ',', '.'), '0'), ',') }} % IVA
                                        ({{ str_replace('_', ' ', $tarifa->periodicidad) }}{{ $tarifa->vigente_desde > now()->toDateString() ? ', desde el ' . \Carbon\Carbon::parse($tarifa->vigente_desde)->format('d/m/Y') : '' }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @if($tarifas->isEmpty())
                        <p class="form-help">Ningún curso tiene tarifa todavía (las crea la Persona 1). Se puede matricular sin ella.</p>
                    @endif
                    @error('tarifa_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- NUEVO (SCRUM-358): descuento opcional de la empresa. --}}
                <div class="form-group">
                    <label class="form-label" for="descuento_id">Descuento</label>
                    <select class="form-select" id="descuento_id" name="descuento_id">
                        <option value="">Sin descuento</option>
                        @foreach($descuentos as $descuento)
                            <option value="{{ $descuento->id }}" @selected((string) old('descuento_id') === (string) $descuento->id)>
                                {{ $descuento->nombre }} ({{ $descuento->tipo === 'porcentaje' ? rtrim(rtrim(number_format((float) $descuento->valor, 2, ',', '.'), '0'), ',') . ' %' : number_format((float) $descuento->valor, 2, ',', '.') . ' €' }})
                            </option>
                        @endforeach
                    </select>
                    @error('descuento_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha_alta">Fecha de alta</label>
                    <input class="form-control" id="fecha_alta" name="fecha_alta" type="date" value="{{ old('fecha_alta', now()->format('Y-m-d')) }}">
                    @error('fecha_alta')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group full">
                    <label class="form-label" for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                    @error('observaciones')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('alumnos.edit', $alumno) }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Matricular</button>
            </div>
        </form>
    </div>
</div>
@endsection
