<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

use App\Models\CategoriasCurso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de CATEGORÍAS DE CURSO (catálogo). Es el CRUD más sencillo posible:
// solo nombre y activo. No hay borrado: una categoría que ya no se usa se desactiva.
class CategoriasCursoController extends Controlador
{
    // LISTADO (ruta GET /categorias-curso).
    public function index(): View
    {
        return view('categorias_curso.index', [
            'categorias' => CategoriasCurso::query()->orderBy('nombre')->get(),
        ]);
    }

    // FORMULARIO VACÍO (ruta GET /categorias-curso/crear).
    public function create(): View
    {
        return view('categorias_curso.formulario', [
            'categoria' => new CategoriasCurso(['activo' => true]),
        ]);
    }

    // GUARDAR una nueva (ruta POST /categorias-curso).
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        CategoriasCurso::create($datos + ['persona_id_created' => $this->usuarioRolId()]);

        return redirect()->route('categorias_curso.index')->with('success', 'Categoría creada.');
    }

    // FORMULARIO para modificar (ruta GET /categorias-curso/{categoria}/editar).
    public function edit(CategoriasCurso $categoria): View
    {
        return view('categorias_curso.formulario', [
            'categoria' => $categoria,
        ]);
    }

    // GUARDAR los cambios (ruta PUT /categorias-curso/{categoria}).
    public function update(Request $request, CategoriasCurso $categoria): RedirectResponse
    {
        $datos = $this->validar($request, $categoria);

        $categoria->update($datos + ['persona_id_updated' => $this->usuarioRolId()]);

        return redirect()->route('categorias_curso.index')->with('success', 'Categoría actualizada.');
    }

    // COMPROBAR el formulario. $categoria: la que se edita (vacía al crear).
    private function validar(Request $request, ?CategoriasCurso $categoria = null): array
    {
        $datos = $request->validate([
            // unique: no puede haber dos categorías con el mismo nombre
            // (al editar, sin contar la propia: ->ignore()).
            'nombre' => ['required', 'string', 'max:80',
                Rule::unique('categorias_curso', 'nombre')->ignore($categoria?->id)],
        ], [
            'nombre.required' => 'Introduce el nombre.',
            'nombre.unique' => 'Ya hay una categoría con ese nombre.',
        ]);

        // La casilla "Activa": si no se marca, no llega y vale false.
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
