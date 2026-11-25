<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

use App\Models\Alumnos;
use App\Models\Parentescos;
use App\Models\Personas;
use App\Models\Tutores;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de TUTORES (Jira: SCRUM-210 Registrar tutores).
// Es el mismo molde que alumnos y profesores: persona + tutor, con ficha única por documento.
// La tabla tutores no tiene más campos que la persona, así que el formulario son solo datos personales.
// Los alumnos a su cargo se gestionan en su ficha (TutorAlumnoController).
class TutoresController extends Controlador
{
    // LISTADO con buscador (ruta GET /tutores).
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $tutores = Tutores::query()
            ->join('personas', 'personas.id', '=', 'tutores.persona_id')
            ->select('tutores.*')
            // Trae su persona y los alumnos a su cargo (con la persona de cada alumno).
            ->with(['persona', 'alumnosACargo.alumno.persona'])
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(function ($donde) use ($buscar) {
                    $donde->where('personas.nombre', 'like', "%{$buscar}%")
                        ->orWhere('personas.apellidos', 'like', "%{$buscar}%")
                        ->orWhere('personas.numero_documento', 'like', "%{$buscar}%")
                        ->orWhere('personas.telefono', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('personas.apellidos')
            ->orderBy('personas.nombre')
            ->get();

        return view('tutores.index', ['tutores' => $tutores, 'buscar' => $buscar]);
    }

    // FORMULARIO VACÍO (ruta GET /tutores/crear).
    // Si viene de la ficha de un alumno (?alumno_id=5), se recuerda para asociarlo después.
    public function create(Request $request): View
    {
        return view('tutores.formulario', $this->datosFormulario(new Tutores(), new Personas(), $request->query('alumno_id')));
    }

    // GUARDAR el tutor nuevo (ruta POST /tutores).
    public function store(Request $request): RedirectResponse
    {
        $datosPersona = $this->validar($request);

        // FICHA ÚNICA, como en alumnos y profesores: ¿existe ya la persona por su documento?
        $persona = null;
        if ($datosPersona['numero_documento']) {
            $persona = Personas::query()
                ->where('tipo_documento', $datosPersona['tipo_documento'])
                ->where('numero_documento', $datosPersona['numero_documento'])
                ->first();
        }

        if ($persona && $persona->tutor) {
            return back()->withInput()->withErrors([
                'numero_documento' => 'Ya hay un tutor con ese documento: ' . $persona->nombreCompleto() . '.',
            ]);
        }

        if (!$persona && !$request->boolean('es_otra_persona')) {
            $parecida = Personas::query()
                ->where('nombre', $datosPersona['nombre'])
                ->where('apellidos', $datosPersona['apellidos'])
                ->whereDate('fecha_nacimiento', $datosPersona['fecha_nacimiento'])
                ->first();

            if ($parecida) {
                return back()->withInput()
                    ->with('posible_duplicado', true)
                    ->with('warning', 'Ya existe una persona llamada ' . $parecida->nombreCompleto()
                        . ' con la misma fecha de nacimiento. Si es otra persona, marca "Es otra persona" y vuelve a guardar.');
            }
        }

        try {
            $tutor = DB::transaction(function () use ($persona, $datosPersona) {
                $usuarioRolId = $this->usuarioRolId();

                // Si la persona ya existía (por ejemplo, era profesora), se reutiliza.
                if (!$persona) {
                    $persona = Personas::create($datosPersona + ['persona_id_created' => $usuarioRolId]);
                }

                return Tutores::create([
                    'persona_id' => $persona->id,
                    'persona_id_created' => $usuarioRolId,
                ]);
            });
        } catch (QueryException $e) {
            return back()->withInput()->withErrors(['general' => 'No se ha podido guardar. Revisa los datos o avisa al equipo.']);
        }

        // A su ficha, para asociarle alumnos. Si venía de un alumno, ese alumno queda preseleccionado.
        return redirect()->route('tutores.edit', ['tutor' => $tutor, 'alumno_id' => $request->input('alumno_id')])
            ->with('success', 'Tutor dado de alta. Ahora indica de qué alumnos es tutor.');
    }

    // FICHA del tutor (ruta GET /tutores/{tutor}/editar).
    public function edit(Request $request, Tutores $tutor): View
    {
        return view('tutores.formulario', $this->datosFormulario($tutor, $tutor->persona, $request->query('alumno_id')));
    }

    // GUARDAR los cambios de sus datos personales (ruta PUT /tutores/{tutor}).
    public function update(Request $request, Tutores $tutor): RedirectResponse
    {
        $datosPersona = $this->validar($request, $tutor->persona);

        $tutor->persona->update($datosPersona + ['persona_id_updated' => $this->usuarioRolId()]);

        return redirect()->route('tutores.edit', $tutor)->with('success', 'Los datos del tutor se han actualizado.');
    }

    // Lo que necesita la vista. $alumnoId: el alumno que hay que dejar elegido en el formulario de asociar.
    private function datosFormulario(Tutores $tutor, Personas $persona, $alumnoId): array
    {
        return [
            'tutor' => $tutor,
            'persona' => $persona,
            'alumnoId' => $alumnoId,
            'alumnosACargo' => $tutor->exists
                ? $tutor->alumnosACargo()->with(['alumno.persona', 'parentesco'])->get() : collect(),
            // Para el desplegable de asociar: alumnos activos, ordenados por apellidos.
            'alumnos' => Alumnos::query()
                ->join('personas', 'personas.id', '=', 'alumnos.persona_id')
                ->select('alumnos.*')
                ->with('persona')
                ->where('alumnos.activo', true)
                ->orderBy('personas.apellidos')->orderBy('personas.nombre')
                ->get(),
            'parentescos' => Parentescos::query()->where('activo', true)->orderBy('id')->get(),
        ];
    }

    // COMPROBAR el formulario (solo datos personales). Devuelve los datos de la persona.
    private function validar(Request $request, ?Personas $persona = null): array
    {
        $request->merge([
            'numero_documento' => $request->filled('numero_documento')
                ? mb_strtoupper(str_replace(' ', '', $request->input('numero_documento'))) : null,
        ]);

        $reglasDocumento = ['nullable', 'string', 'max:20', 'required_with:tipo_documento'];
        if ($persona) {
            $reglasDocumento[] = Rule::unique('personas', 'numero_documento')
                ->where('tipo_documento', $request->input('tipo_documento'))
                ->ignore($persona->id);
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:180'],
            'tipo_documento' => ['nullable', 'in:dni,nie,pasaporte', 'required_with:numero_documento'],
            'numero_documento' => $reglasDocumento,
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'correo_electronico' => ['nullable', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:40'],
        ], [
            'nombre.required' => 'Introduce el nombre.',
            'apellidos.required' => 'Introduce los apellidos.',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI, NIE o pasaporte.',
            'tipo_documento.required_with' => 'Elige el tipo de documento.',
            'numero_documento.required_with' => 'Escribe el número de documento.',
            'numero_documento.unique' => 'Ese documento ya lo tiene otra persona.',
            'fecha_nacimiento.required' => 'Introduce la fecha de nacimiento.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'correo_electronico.email' => 'Introduce un email válido.',
        ]);

        return [
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'tipo_documento' => $datos['tipo_documento'] ?? null,
            'numero_documento' => $datos['numero_documento'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'correo_electronico' => $datos['correo_electronico'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
        ];
    }
}
