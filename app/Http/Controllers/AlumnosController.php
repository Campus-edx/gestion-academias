<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Los dos modelos que usa esta pantalla.
use App\Models\Alumnos;
// use App\Models\Inscripciones; TAREA DE MAS ADELANTE, INSCRIPCIONES
use App\Models\Personas;
// Piezas de Laravel: errores de la base de datos, respuestas, petición,
// transacciones, reglas de validación y vistas.
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de ALUMNOS (Jira: SCRUM-198 Registrar alumnos,
// SCRUM-204 Consultar y modificar alumnos, SCRUM-224 Mantener fichas únicas).
// Un alumno se guarda en DOS tablas: personas (datos personales) y alumnos (la ficha de alumno).
class AlumnosController extends Controlador
{
    // LISTADO de alumnos, con buscador (ruta GET /alumnos).
    public function index(Request $request): View
    {
        // Lo que se haya escrito en el buscador (?buscar=...). Vacío si no se ha buscado nada.
        $buscar = trim((string) $request->query('buscar', ''));

        // Consulta de alumnos:
        $alumnos = Alumnos::query()
            // Se une con personas para poder buscar y ordenar por nombre y apellidos.
            ->join('personas', 'personas.id', '=', 'alumnos.persona_id')
            // Solo las columnas de alumnos (las de la persona llegan con ->with).
            ->select('alumnos.*')
            // Trae también la persona de cada alumno, en una sola consulta.
            ->with('persona')
            // when(): este filtro solo se aplica si se ha escrito algo en el buscador.
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                // Busca el texto dentro del nombre, de los apellidos o del documento.
                $consulta->where(function ($donde) use ($buscar) {
                    $donde->where('personas.nombre', 'like', "%{$buscar}%")
                        ->orWhere('personas.apellidos', 'like', "%{$buscar}%")
                        ->orWhere('personas.numero_documento', 'like', "%{$buscar}%");
                });
            })
            // Ordenados por apellidos y después por nombre.
            ->orderBy('personas.apellidos')
            ->orderBy('personas.nombre')
            ->get();

        // Muestra la vista alumnos/index.blade.php con la lista y lo buscado.
        return view('alumnos.index', [
            'alumnos' => $alumnos,
            'buscar' => $buscar,
        ]);
    }

    // FORMULARIO VACÍO para dar de alta un alumno (ruta GET /alumnos/crear).
    public function create(): View
    {
        // Se pasan un alumno y una persona nuevos (vacíos): el mismo formulario sirve para editar.
        return view('alumnos.formulario', [
            'alumno' => new Alumnos(['activo' => true]),
            'persona' => new Personas(),
        ]);
    }

    // GUARDAR el alumno nuevo (ruta POST /alumnos).
    public function store(Request $request): RedirectResponse
    {
        // 1. Comprobar los datos del formulario (si algo falla, vuelve al formulario con los errores).
        [$datosPersona, $datosAlumno] = $this->validar($request);

        // 2. FICHA ÚNICA (SCRUM-224): ¿existe ya una persona con ese mismo documento?
        $persona = null;
        if ($datosPersona['numero_documento']) {
            $persona = Personas::query()
                ->where('tipo_documento', $datosPersona['tipo_documento'])
                ->where('numero_documento', $datosPersona['numero_documento'])
                ->first();
        }

        // 2a. Si existe y YA es alumno: no se crea otro; se avisa.
        if ($persona && $persona->alumno) {
            return back()->withInput()->withErrors([
                'numero_documento' => 'Ya hay un alumno con ese documento: ' . $persona->nombreCompleto() . '.',
            ]);
        }

        // 2b. Si no hay documento repetido, se mira si hay alguien con el mismo nombre,
        //     apellidos y fecha de nacimiento. Puede ser la misma persona sin documento.
        //     Solo se avisa una vez: si el usuario marca "Es otra persona", se sigue adelante.
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

        // 3. Guardar. DB::transaction hace que se guarden las dos tablas o ninguna:
        //    si falla el alumno, tampoco se queda guardada la persona.
        try {
            DB::transaction(function () use ($persona, $datosPersona, $datosAlumno) {
                $usuarioRolId = $this->usuarioRolId();

                // Si la persona no existía, se crea. Si existía (por ejemplo, ya era tutor),
                // se reutiliza tal cual y no se tocan sus datos.
                if (!$persona) {
                    $persona = Personas::create($datosPersona + ['persona_id_created' => $usuarioRolId]);
                }

                // Se crea la ficha de alumno de esa persona.
                Alumnos::create($datosAlumno + [
                    'persona_id' => $persona->id,
                    'persona_id_created' => $usuarioRolId,
                ]);
            });
        } catch (QueryException $e) {
            // Si la base de datos rechaza algo, se vuelve al formulario con su mensaje.
            return back()->withInput()->withErrors(['general' => $this->mensajeError($e)]);
        }

        // 4. Volver al listado con un mensaje verde.
        return redirect()->route('alumnos.index')->with('success', 'El alumno se ha dado de alta correctamente.');
    }

    // FORMULARIO para modificar un alumno (ruta GET /alumnos/{alumno}/editar).
    // Laravel busca solo el alumno con ese id y lo pasa en $alumno.
    public function edit(Alumnos $alumno): View
    {
            return view('alumnos.formulario', [
            'alumno' => $alumno,
            'persona' => $alumno->persona,

        // ------------OJO AL DATO-------- 
            // TAREA: ALUMNOS ESTE FORMULARIO DEPENDE DE OTRO TAREA DE 
            // MAS ADELANTE , EL USE DEL MODELO DE INCRIPCIONES HAY QUE DESCOMENTARLO
        //--------OJO POR OJO, DIENTE POR DIENTE-----

            // NUEVO (capítulo de tutores): sus tutores, con persona y parentesco, para la tarjeta "Tutores".
            // 'tutoresDelAlumno' => $alumno->tutoresDelAlumno()->with(['tutor.persona', 'parentesco'])->get(),
            // NUEVO (capítulo de inscripciones): sus matrículas en grupos de la empresa del usuario.
            // 'inscripciones' => Inscripciones::historial()
            //     ->where('i.alumno_id', $alumno->id)
            //     ->where('e.id', auth()->user()->empresa_id)
            //     ->get(),
        ]);
    }

    // GUARDAR los cambios de un alumno (ruta PUT /alumnos/{alumno}).
    public function update(Request $request, Alumnos $alumno): RedirectResponse
    {
        // Se comprueba el formulario. Se pasa la persona para que no choque con su propio documento.
        [$datosPersona, $datosAlumno] = $this->validar($request, $alumno->persona);

        try {
            DB::transaction(function () use ($alumno, $datosPersona, $datosAlumno) {
                $usuarioRolId = $this->usuarioRolId();

                // Se actualizan las dos tablas: los datos personales y la ficha de alumno.
                $alumno->persona->update($datosPersona + ['persona_id_updated' => $usuarioRolId]);
                $alumno->update($datosAlumno + ['persona_id_updated' => $usuarioRolId]);
            });
        } catch (QueryException $e) {
            return back()->withInput()->withErrors(['general' => $this->mensajeError($e)]);
        }

        return redirect()->route('alumnos.index')->with('success', 'Los datos del alumno se han actualizado.');
    }

    // COMPROBAR el formulario (lo usan store y update).
    // Devuelve dos listas: los datos de la persona y los datos del alumno.
    // $persona: la persona que se está editando (vacío al dar de alta).
    private function validar(Request $request, ?Personas $persona = null): array
    {
        // El documento se guarda siempre igual: en mayúsculas y sin espacios (12345678z → 12345678Z).
        $request->merge([
            'numero_documento' => $request->filled('numero_documento')
                ? mb_strtoupper(str_replace(' ', '', $request->input('numero_documento')))
                : null,
        ]);

        // Regla del documento. Al EDITAR, no puede tenerlo otra persona (sin contar a ella misma).
        // Al DAR DE ALTA no se usa esta regla: si ya existe, se reutiliza la persona (ficha única).
        $reglasDocumento = ['nullable', 'string', 'max:20', 'required_with:tipo_documento'];
        if ($persona) {
            $reglasDocumento[] = Rule::unique('personas', 'numero_documento')
                ->where('tipo_documento', $request->input('tipo_documento'))
                ->ignore($persona->id);
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:180'],
            // in: solo admite esos tres valores. required_with: si hay número, hace falta el tipo.
            'tipo_documento' => ['nullable', 'in:dni,nie,pasaporte', 'required_with:numero_documento'],
            'numero_documento' => $reglasDocumento,
            // before_or_equal:today = no puede ser una fecha futura.
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'correo_electronico' => ['nullable', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            // Mensajes de error en castellano.
            'nombre.required' => 'Introduce el nombre.',
            'apellidos.required' => 'Introduce los apellidos.',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI, NIE o pasaporte.',
            'tipo_documento.required_with' => 'Elige el tipo de documento.',
            'numero_documento.required_with' => 'Escribe el número de documento.',
            'numero_documento.unique' => 'Ese documento ya lo tiene otra persona.',
            'fecha_nacimiento.required' => 'Introduce la fecha de nacimiento.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'correo_electronico.email' => 'Introduce un email válido.',
        ]);

        // Se reparten los datos: unos van a personas y otros a alumnos.
        $datosPersona = [
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'tipo_documento' => $datos['tipo_documento'] ?? null,
            'numero_documento' => $datos['numero_documento'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'correo_electronico' => $datos['correo_electronico'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
        ];
        $datosAlumno = [
            'observaciones' => $datos['observaciones'] ?? null,
            // La casilla "Activo": si no se marca, el navegador no la envía y vale false.
            // Desmarcarla es la BAJA LÓGICA: el alumno no se borra, queda inactivo.
            'activo' => $request->boolean('activo'),
        ];

        return [$datosPersona, $datosAlumno];
    }

    // Convierte un error de la base de datos en un mensaje para el usuario.
    private function mensajeError(QueryException $e): string
    {
        // Las reglas de nuestros triggers (código 45000) ya traen su mensaje en castellano.
        if ($e->getCode() === '45000') {
            return $e->errorInfo[2] ?? 'La base de datos ha rechazado los datos.';
        }

        // Clave repetida (por ejemplo, el mismo documento dos veces).
        if ($e->getCode() === '23000') {
            return 'Ya existe un registro con esos datos (revisa el documento).';
        }

        return 'No se ha podido guardar. Inténtalo de nuevo o avisa al equipo.';
    }
}
