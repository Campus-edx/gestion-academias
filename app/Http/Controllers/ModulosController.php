<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

use App\Models\Materias;
use App\Models\Modulos;
use App\Models\Niveles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de MÓDULOS (los contenidos que forman los cursos: Inglés B1, Matemáticas 2º ESO...).
// Parte de SCRUM-255 Crear y gestionar cursos: sin módulos no se puede montar un curso.
class ModulosController extends Controlador
{
    // LISTADO (ruta GET /modulos). Con su materia, su nivel y la empresa que lo creó.
    public function index(): View
    {
        return view('modulos.index', [
            'modulos' => Modulos::query()->with(['materia', 'nivel', 'empresa'])->orderBy('nombre')->get(),
        ]);
    }

    // FORMULARIO VACÍO (ruta GET /modulos/crear).
    public function create(): View
    {
        return view('modulos.formulario', $this->datosFormulario(new Modulos(['activo' => true])));
    }

    // GUARDAR uno nuevo (ruta POST /modulos).
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        Modulos::create($datos + [
            // El módulo es de la empresa del usuario que lo crea.
            'empresa_id' => $request->user()->empresa_id,
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('modulos.index')->with('success', 'Módulo creado.');
    }

    // FORMULARIO para modificar (ruta GET /modulos/{modulo}/editar).
    public function edit(Modulos $modulo): View
    {
        return view('modulos.formulario', $this->datosFormulario($modulo));
    }

    // GUARDAR los cambios (ruta PUT /modulos/{modulo}).
    public function update(Request $request, Modulos $modulo): RedirectResponse
    {
        $datos = $this->validar($request);

        $modulo->update($datos + ['persona_id_updated' => $this->usuarioRolId()]);

        return redirect()->route('modulos.index')->with('success', 'Módulo actualizado.');
    }

    // Lo que necesita el formulario: el módulo y las listas de los desplegables.
    private function datosFormulario(Modulos $modulo): array
    {
        return [
            'modulo' => $modulo,
            // Solo las materias activas para elegir... más la que ya tenga el módulo, por si se desactivó.
            'materias' => Materias::query()
                ->where(fn ($q) => $q->where('activo', true)->orWhere('id', $modulo->materia_id))
                ->orderBy('nombre')->get(),
            // Los niveles se ordenan por su campo "orden" (1º Primaria, 2º Primaria...).
            'niveles' => Niveles::query()->where('activo', true)->orderBy('orden')->get(),
        ];
    }

    // COMPROBAR el formulario.
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            // exists: el id tiene que existir en esa tabla.
            'materia_id' => ['required', 'integer', Rule::exists('materias', 'id')],
            'nivel_id' => ['nullable', 'integer', Rule::exists('niveles', 'id')],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ], [
            'nombre.required' => 'Introduce el nombre del módulo.',
            'materia_id.required' => 'Elige la materia. Si no hay ninguna, créala antes en Materias.',
        ]);

        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
