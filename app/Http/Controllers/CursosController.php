<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

use App\Models\CategoriasCurso;
use App\Models\Cursos;
use App\Models\Modulos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de CURSOS (Jira: SCRUM-255 Crear y gestionar cursos).
// Un curso es la oferta de la empresa: nombre, categoría, modalidad, alumnos por grupo...
// Sus módulos y su equipamiento se añaden desde la ficha (otros dos controladores pequeños).
// Cuando esto esté en main, la Persona 3 ya puede crear grupos de estos cursos.
class CursosController extends Controlador
{
    // LISTADO de los cursos de la empresa del usuario (ruta GET /cursos).
    public function index(Request $request): View
    {
        $cursos = Cursos::query()
            // La vista v_cursos_horas calcula las horas totales de cada curso (suma de sus módulos).
            // leftJoin: también salen los cursos que aún no tienen módulos (con 0 horas).
            ->leftJoin('v_cursos_horas as h', 'h.curso_id', '=', 'cursos.id')
            ->select('cursos.*', 'h.horas_totales', 'h.modulos as numero_modulos')
            ->with('categoria')
            // Cada empresa ve sus cursos.
            ->where('cursos.empresa_id', $request->user()->empresa_id)
            ->orderBy('cursos.nombre')
            ->get();

        return view('cursos.index', ['cursos' => $cursos]);
    }

    // FORMULARIO VACÍO (ruta GET /cursos/crear). Valores por defecto: presencial, mínimo 3 alumnos.
    public function create(): View
    {
        return view('cursos.formulario', $this->datosFormulario(
            new Cursos(['activo' => true, 'modalidad' => 'presencial', 'min_alumnos' => 3])
        ));
    }

    // GUARDAR uno nuevo (ruta POST /cursos).
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $curso = Cursos::create($datos + [
            'empresa_id' => $request->user()->empresa_id,
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        // A la ficha, para añadirle los módulos.
        return redirect()->route('cursos.edit', $curso)
            ->with('success', 'Curso creado. Ahora añade sus módulos.');
    }

    // FICHA del curso (ruta GET /cursos/{curso}/editar).
    public function edit(Request $request, Cursos $curso): View
    {
        // Seguridad: solo los cursos de la empresa del usuario (si no, "no encontrado").
        abort_if((int) $curso->empresa_id !== (int) $request->user()->empresa_id, 404);

        return view('cursos.formulario', $this->datosFormulario($curso));
    }

    // GUARDAR los cambios (ruta PUT /cursos/{curso}).
    public function update(Request $request, Cursos $curso): RedirectResponse
    {
        abort_if((int) $curso->empresa_id !== (int) $request->user()->empresa_id, 404);

        $datos = $this->validar($request);

        $curso->update($datos + ['persona_id_updated' => $this->usuarioRolId()]);

        return redirect()->route('cursos.edit', $curso)->with('success', 'Curso actualizado.');
    }

    // Lo que necesita la vista: el curso, los desplegables y, en la ficha, sus módulos y equipamiento.
    private function datosFormulario(Cursos $curso): array
    {
        return [
            'curso' => $curso,
            'categorias' => CategoriasCurso::query()
                ->where(fn ($q) => $q->where('activo', true)->orWhere('id', $curso->categoria_curso_id))
                ->orderBy('nombre')->get(),
            // Catálogos que ya vienen rellenos en el script: se leen sin modelo propio.
            'tiposEspacio' => DB::table('tipos_espacio')->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'equipamientos' => DB::table('equipamientos')->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'modulos' => Modulos::query()->where('activo', true)->orderBy('nombre')->get(),
            // Solo en la ficha (curso ya guardado):
            'modulosDelCurso' => $curso->exists
                ? $curso->modulosDelCurso()->with('modulo')->orderBy('orden')->get() : collect(),
            'equipamientoDelCurso' => $curso->exists
                ? DB::table('curso_equipamiento as ce')
                    ->join('equipamientos as e', 'e.id', '=', 'ce.equipamiento_id')
                    ->where('ce.curso_id', $curso->id)
                    ->orderBy('e.nombre')
                    ->get(['ce.id', 'e.nombre', 'ce.cantidad'])
                : collect(),
            // Horas totales, de la vista v_cursos_horas (0 si es nuevo).
            'horasTotales' => $curso->exists
                ? (int) DB::table('v_cursos_horas')->where('curso_id', $curso->id)->value('horas_totales') : 0,
        ];
    }

    // COMPROBAR el formulario.
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'categoria_curso_id' => ['required', 'integer', 'exists:categorias_curso,id'],
            'modalidad' => ['required', 'in:presencial,online,mixta'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'tipo_espacio_id' => ['nullable', 'integer', 'exists:tipos_espacio,id'],
            // Edades opcionales. gte: mayor o igual que el otro campo.
            // Rule::when: la comparación solo se aplica si se ha escrito la edad mínima
            // (si la mínima está vacía, Laravel daría error al comparar con "nada").
            'edad_minima' => ['nullable', 'integer', 'between:0,120'],
            'edad_maxima' => ['nullable', 'integer', 'between:0,120',
                Rule::when($request->filled('edad_minima'), ['gte:edad_minima'])],
            // Alumnos por grupo: la base de datos también exige máximo ≥ mínimo.
            'min_alumnos' => ['required', 'integer', 'between:1,255'],
            'max_alumnos' => ['required', 'integer', 'between:1,255', 'gte:min_alumnos'],
        ], [
            'nombre.required' => 'Introduce el nombre del curso.',
            'categoria_curso_id.required' => 'Elige la categoría. Si no hay ninguna, créala antes en Categorías de curso.',
            'modalidad.in' => 'La modalidad debe ser presencial, online o mixta.',
            'edad_maxima.gte' => 'La edad máxima no puede ser menor que la mínima.',
            'min_alumnos.required' => 'Indica el mínimo de alumnos para abrir un grupo.',
            'max_alumnos.required' => 'Indica el máximo de alumnos por grupo.',
            'max_alumnos.gte' => 'El máximo de alumnos no puede ser menor que el mínimo.',
        ]);

        // Un curso online no necesita aula: se borra el tipo de aula si se eligió.
        if ($datos['modalidad'] === 'online') {
            $datos['tipo_espacio_id'] = null;
        }

        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
