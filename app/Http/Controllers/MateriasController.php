<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

use App\Models\Materias;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de MATERIAS (catálogo). Es una copia de CategoriasCursoController
// cambiando "categoría" por "materia": mismo molde, otra tabla.
class MateriasController extends Controlador
{
    // LISTADO (ruta GET /materias).
    public function index(): View
    {
        return view('materias.index', [
            'materias' => Materias::query()->orderBy('nombre')->get(),
        ]);
    }

    // FORMULARIO VACÍO (ruta GET /materias/crear).
    public function create(): View
    {
        return view('materias.formulario', [
            'materia' => new Materias(['activo' => true]),
        ]);
    }

    // GUARDAR una nueva (ruta POST /materias).
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        Materias::create($datos + ['persona_id_created' => $this->usuarioRolId()]);

        return redirect()->route('materias.index')->with('success', 'Materia creada.');
    }

    // FORMULARIO para modificar (ruta GET /materias/{materia}/editar).
    public function edit(Materias $materia): View
    {
        return view('materias.formulario', [
            'materia' => $materia,
        ]);
    }

    // GUARDAR los cambios (ruta PUT /materias/{materia}).
    public function update(Request $request, Materias $materia): RedirectResponse
    {
        $datos = $this->validar($request, $materia);

        $materia->update($datos + ['persona_id_updated' => $this->usuarioRolId()]);

        return redirect()->route('materias.index')->with('success', 'Materia actualizada.');
    }

    // COMPROBAR el formulario.
    private function validar(Request $request, ?Materias $materia = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80',
                Rule::unique('materias', 'nombre')->ignore($materia?->id)],
        ], [
            'nombre.required' => 'Introduce el nombre.',
            'nombre.unique' => 'Ya hay una materia con ese nombre.',
        ]);

        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
