{{-- Usa la plantilla común (menú lateral y barra superior). --}}
@extends('plantillas.principal')

{{-- Título de la pestaña: "Editar sede" si la sede ya existe, "Nueva sede" si no. --}}
@section('title', $sede->exists ? 'Editar sede' : 'Nueva sede')
{{-- Título de la barra superior. --}}
@section('topbar', 'Sedes')

{{-- Contenido de esta pantalla: --}}
@section('content')

{{-- Encabezado con el mismo título. --}}
<div class="page-heading">
    <div>
        <h1>{{ $sede->exists ? 'Editar sede' : 'Nueva sede' }}</h1>
    </div>
</div>

{{-- Tarjeta con el formulario. --}}
<div class="card">
    <div class="card-body">

        {{-- Este formulario sirve para crear y para editar:
             si la sede existe se envía a sedes.update (guardar cambios);
             si es nueva, a sedes.store (guardar nueva). --}}
        <form method="POST" action="{{ $sede->exists ? route('sedes.update', $sede) : route('sedes.store') }}">
            {{-- Código de seguridad obligatorio. --}}
            @csrf
            {{-- Si estamos editando, el envío se convierte en PUT (modificar). --}}
            @if($sede->exists)
                @method('PUT')
            @endif

            <div class="form-grid">

                {{-- Bucle: crea un campo de texto por cada elemento de esta lista.
                     A la izquierda, el nombre del campo en la base de datos;
                     a la derecha, el texto que ve el usuario. --}}
                @foreach([
                    'nombre' => 'Nombre',
                    'direccion' => 'Dirección',
                    'codigo_postal' => 'Código postal',
                    'localidad' => 'Localidad',
                    'telefono' => 'Teléfono',
                    'correo_electronico' => 'Email',
                ] as $campo => $etiqueta)
                    <div class="form-group">
                        {{-- Texto que aparece encima del campo. --}}
                        <label class="form-label" for="{{ $campo }}">{{ $etiqueta }}</label>
                        {{-- El campo. old() recupera lo escrito si hubo un error;
                             si no, muestra el valor actual de la sede. --}}
                        <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="text"
                               value="{{ old($campo, $sede->$campo) }}">
                        {{-- Mensaje de error de ese campo, si lo hay. --}}
                        @error($campo)<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach

                {{-- Desplegable para elegir el tipo de sede.
                     @selected marca la opción que corresponde. --}}
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="propia" @selected(old('tipo', $sede->tipo) === 'propia')>Propia</option>
                        <option value="externa" @selected(old('tipo', $sede->tipo) === 'externa')>Externa</option>
                    </select>
                </div>

                {{-- Casilla "Activa". @checked la deja marcada si la sede está activa. --}}
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activa" value="1" @checked(old('activa', $sede->activa))>
                        Activa
                    </label>
                </div>
            </div>

            {{-- Botones: Cancelar vuelve al listado sin guardar; Guardar envía el formulario. --}}
            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('sedes.index') }}">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection