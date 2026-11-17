<?php

// Dirección del archivo dentro del proyecto: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Piezas que usa este controlador.
use App\Models\Sedes;                    // El modelo de la tabla sedes.
use Illuminate\Http\RedirectResponse;    // Respuesta "vete a otra página".
use Illuminate\Http\Request;             // La petición del navegador (datos del formulario, usuario...).
use Illuminate\View\View;                // Respuesta "muestra esta pantalla".

// Controlador de sedes: listar, crear, editar y borrar sedes.
// Hereda de Controlador, como todos los controladores del proyecto.
class SedesController extends Controlador
{
    // LISTADO de sedes (ruta GET /sedes).
    public function index(): View
    {
        // Muestra la vista sedes/index.blade.php con todas las sedes ordenadas por nombre.
        return view('sedes.index', [
            'sedes' => Sedes::query()->orderBy('nombre')->get(),
        ]);
    }

    // FORMULARIO VACÍO para crear una sede (ruta GET /sedes/crear).
    public function create(): View
    {
        // Muestra sedes/formulario.blade.php con una sede nueva, aún sin guardar.
        // Lleva "propia" y "activa" marcadas de entrada.
        return view('sedes.formulario', [
            'sede' => new Sedes(['tipo' => 'propia', 'activa' => true]),
        ]);
    }

    // GUARDAR una sede nueva (ruta POST /sedes).
    public function store(Request $request): RedirectResponse
    {
        // Comprueba los datos del formulario con el método validar() de abajo.
        $datos = $this->validar($request);

        // Añade los datos que no vienen del formulario:
        $datos['empresa_id'] = $request->user()->empresa_id;  // La empresa del usuario conectado.
        $datos['persona_id_created'] = $request->user()->id;  // Quién la crea.

        // Guarda la sede en la tabla sedes.
        Sedes::create($datos);

        // Vuelve al listado con un mensaje verde de éxito.
        return redirect()->route('sedes.index')->with('success', 'La sede se ha creado correctamente.');
    }

    // FORMULARIO DE EDICIÓN de una sede (ruta GET /sedes/{sede}/editar).
    // Laravel busca solo la sede por su id y la entrega en $sede.
    public function edit(Sedes $sede): View
    {
        // Muestra el mismo formulario, pero relleno con los datos de esa sede.
        return view('sedes.formulario', [
            'sede' => $sede,
        ]);
    }

    // GUARDAR LOS CAMBIOS de una sede (ruta PUT /sedes/{sede}).
    public function update(Request $request, Sedes $sede): RedirectResponse
    {
        // Comprueba los datos del formulario.
        $datos = $this->validar($request);

        // Quién la modifica.
        $datos['persona_id_updated'] = $request->user()->id;

        // Guarda los cambios.
        $sede->update($datos);

        // Vuelve al listado con mensaje de éxito.
        return redirect()->route('sedes.index')->with('success', 'La sede se ha actualizado correctamente.');
    }

    // BORRAR una sede (ruta DELETE /sedes/{sede}).
    public function destroy(Sedes $sede): RedirectResponse
    {
        // La elimina de la tabla.
        $sede->delete();

        // Vuelve al listado con mensaje.
        return redirect()->route('sedes.index')->with('success', 'La sede se ha eliminado.');
    }

    // VALIDAR: comprueba los datos del formulario. Lo usan store() y update(),
    // así las reglas se escriben una sola vez.
    // "private" = solo se puede usar desde dentro de este controlador.
    private function validar(Request $request): array
    {
        // Si algo no cumple las reglas, Laravel vuelve al formulario con los errores.
        $datos = $request->validate([
            // Reglas de cada campo: required = obligatorio, nullable = puede ir vacío,
            // string = texto, max = longitud máxima, email = formato de correo,
            // in = solo puede ser uno de esos valores.
            'nombre' => ['required', 'string', 'max:100'],
            'direccion' => ['required', 'string', 'max:255'],
            'codigo_postal' => ['required', 'string', 'max:10'],
            'localidad' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'tipo' => ['required', 'in:propia,externa'],
        ], [
            // Mensajes de error en castellano.
            'nombre.required' => 'Introduce el nombre de la sede.',
            'direccion.required' => 'Introduce la dirección.',
            'codigo_postal.required' => 'Introduce el código postal.',
            'localidad.required' => 'Introduce la localidad.',
            'correo_electronico.email' => 'Introduce un email válido.',
            'tipo.in' => 'El tipo debe ser propia o externa.',
        ]);

        // La casilla "Activa": si está marcada llega 1 (true); si no, no llega nada (false).
        $datos['activa'] = $request->boolean('activa');

        // Devuelve los datos ya comprobados.
        return $datos;
    }
}