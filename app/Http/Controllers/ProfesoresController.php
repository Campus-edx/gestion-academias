<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Modelos que usa esta pantalla.
use App\Models\AusenciasProfesores;
use App\Models\Empresas;
use App\Models\Modulos;
use App\Models\Personas;
use App\Models\Profesores;
// Piezas de Laravel.
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de PROFESORES (Jira: SCRUM-228 Registrar profesores,
// SCRUM-233 Gestionar datos profesionales, SCRUM-246 Consultar profesores por empresa).
// Funciona igual que AlumnosController: un profesor son dos filas, persona + profesor.
// Las vinculaciones con empresas y la disponibilidad tienen su propio controlador.
class ProfesoresController extends Controlador
{
    // LISTADO de profesores, con buscador y filtro por empresa (ruta GET /profesores).
    public function index(Request $request): View
    {
        // Lo escrito en el buscador y la empresa elegida en el filtro (vacíos si no hay).
        $buscar = trim((string) $request->query('buscar', ''));
        $empresaId = $request->query('empresa_id');

        $profesores = Profesores::query()
            // Se une con personas para buscar y ordenar por nombre.
            ->join('personas', 'personas.id', '=', 'profesores.persona_id')
            ->select('profesores.*')
            // Trae la persona y sus vinculaciones vigentes (con su empresa) en pocas consultas.
            ->with(['persona', 'vinculacionesVigentes.empresa'])
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(function ($donde) use ($buscar) {
                    $donde->where('personas.nombre', 'like', "%{$buscar}%")
                        ->orWhere('personas.apellidos', 'like', "%{$buscar}%")
                        ->orWhere('personas.numero_documento', 'like', "%{$buscar}%")
                        ->orWhere('profesores.especialidad', 'like', "%{$buscar}%");
                });
            })
            // SCRUM-246: solo los que tienen una vinculación VIGENTE con esa empresa.
            // whereHas = "que tenga al menos una vinculación que cumpla esto".
            ->when($empresaId, function ($consulta) use ($empresaId) {
                $consulta->whereHas('vinculaciones', fn ($v) => $v->where('empresa_id', $empresaId)->vigentes());
            })
            ->orderBy('personas.apellidos')
            ->orderBy('personas.nombre')
            ->get();

        return view('profesores.index', [
            'profesores' => $profesores,
            'buscar' => $buscar,
            'empresaId' => $empresaId,
            'empresas' => Empresas::query()->orderBy('razon_social')->get(),
        ]);
    }

    // FORMULARIO VACÍO para dar de alta un profesor (ruta GET /profesores/crear).
    public function create(): View
    {
        return view('profesores.formulario', $this->datosFormulario(new Profesores(['activo' => true]), new Personas()));
    }

    // GUARDAR el profesor nuevo (ruta POST /profesores).
    public function store(Request $request): RedirectResponse
    {
        [$datosPersona, $datosProfesor, $modulos, $centros] = $this->validar($request);

        // FICHA ÚNICA, igual que en alumnos: ¿existe ya una persona con ese documento?
        $persona = null;
        if ($datosPersona['numero_documento']) {
            $persona = Personas::query()
                ->where('tipo_documento', $datosPersona['tipo_documento'])
                ->where('numero_documento', $datosPersona['numero_documento'])
                ->first();
        }

        // Si ya es profesor: no se crea otro.
        if ($persona && $persona->profesor) {
            return back()->withInput()->withErrors([
                'numero_documento' => 'Ya hay un profesor con ese documento: ' . $persona->nombreCompleto() . '.',
            ]);
        }

        // Sin documento repetido: aviso si hay alguien con el mismo nombre, apellidos y fecha de nacimiento.
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
            // Transacción: persona, profesor, módulos y centros se guardan todos o ninguno.
            $profesor = DB::transaction(function () use ($persona, $datosPersona, $datosProfesor, $modulos, $centros) {
                $usuarioRolId = $this->usuarioRolId();

                // Si la persona no existía, se crea; si existía (era alumno o tutor), se reutiliza.
                if (!$persona) {
                    $persona = Personas::create($datosPersona + ['persona_id_created' => $usuarioRolId]);
                }

                $profesor = Profesores::create($datosProfesor + [
                    'persona_id' => $persona->id,
                    'persona_id_created' => $usuarioRolId,
                ]);

                $this->guardarSeleccion('profesor_modulo', 'modulo_id', $profesor->id, $modulos);
                $this->guardarSeleccion('profesor_centro', 'centro_id', $profesor->id, $centros);

                // Lo que devuelve la función es lo que devuelve DB::transaction.
                return $profesor;
            });
        } catch (QueryException $e) {
            return back()->withInput()->withErrors(['general' => $this->mensajeError($e)]);
        }

        // Se va a su ficha, porque falta lo más importante: su vinculación con la empresa.
        return redirect()->route('profesores.edit', $profesor)
            ->with('success', 'Profesor dado de alta. Ahora añade su vinculación con la empresa.');
    }

    // FICHA del profesor para modificarla (ruta GET /profesores/{profesor}/editar).
    public function edit(Profesores $profesor): View
    {
        return view('profesores.formulario', $this->datosFormulario($profesor, $profesor->persona));
    }

    // GUARDAR los cambios de los datos del profesor (ruta PUT /profesores/{profesor}).
    public function update(Request $request, Profesores $profesor): RedirectResponse
    {
        [$datosPersona, $datosProfesor, $modulos, $centros] = $this->validar($request, $profesor->persona);

        try {
            DB::transaction(function () use ($profesor, $datosPersona, $datosProfesor, $modulos, $centros) {
                $usuarioRolId = $this->usuarioRolId();

                $profesor->persona->update($datosPersona + ['persona_id_updated' => $usuarioRolId]);
                $profesor->update($datosProfesor + ['persona_id_updated' => $usuarioRolId]);

                $this->guardarSeleccion('profesor_modulo', 'modulo_id', $profesor->id, $modulos);
                $this->guardarSeleccion('profesor_centro', 'centro_id', $profesor->id, $centros);
            });
        } catch (QueryException $e) {
            return back()->withInput()->withErrors(['general' => $this->mensajeError($e)]);
        }

        return redirect()->route('profesores.edit', $profesor)->with('success', 'Los datos del profesor se han actualizado.');
    }

    // Todo lo que necesita la vista del formulario (alta y ficha).
    private function datosFormulario(Profesores $profesor, Personas $persona): array
    {
        return [
            'profesor' => $profesor,
            'persona' => $persona,
            // Listas para las casillas: módulos activos y centros activos.
            'modulos' => Modulos::query()->where('activo', true)->orderBy('nombre')->get(),
            // Los centros son de la Persona 3: se leen con una consulta sencilla, sin su modelo.
            'centros' => DB::table('centros')->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            // Qué módulos y centros tiene marcados este profesor (vacío si es nuevo).
            'modulosMarcados' => $profesor->exists
                ? DB::table('profesor_modulo')->where('profesor_id', $profesor->id)->pluck('modulo_id')->all() : [],
            'centrosMarcados' => $profesor->exists
                ? DB::table('profesor_centro')->where('profesor_id', $profesor->id)->pluck('centro_id')->all() : [],
            // Para las secciones de vinculaciones y disponibilidad (solo en la ficha).
            'vinculaciones' => $profesor->exists
                ? $profesor->vinculaciones()->with('empresa')->orderByDesc('fecha_inicio')->get() : collect(),
            'disponibilidades' => $profesor->exists
                ? $profesor->disponibilidades()->orderBy('dia_semana')->orderBy('hora_inicio')->get() : collect(),
            'empresas' => Empresas::query()->where('activo', true)->orderBy('razon_social')->get(),
        ] + $this->datosAusencias($profesor);
    }

    // NUEVO (ausencias): lo que necesita la tarjeta "Ausencias" de la ficha.
    // El "+" de arriba junta las dos listas de datos en una.
    private function datosAusencias(Profesores $profesor): array
    {
        if (! $profesor->exists) {
            return ['ausencias' => collect(), 'afectadas' => [], 'motivosAusencia' => collect()];
        }

        // Sus ausencias, de la más reciente a la más antigua, con el nombre del motivo.
        $ausencias = $profesor->ausencias()
            ->join('motivos_ausencia as m', 'm.id', '=', 'ausencias_profesores.motivo_ausencia_id')
            ->select('ausencias_profesores.*', 'm.nombre as motivo')
            ->orderByDesc('ausencias_profesores.fecha_inicio')
            ->get();

        // Para cada ausencia, sus sesiones afectadas: [id de la ausencia => lista de sesiones].
        $afectadas = [];
        foreach ($ausencias as $ausencia) {
            $afectadas[$ausencia->id] = AusenciasProfesores::sesionesAfectadas(
                $profesor->id,
                $ausencia->fecha_inicio->toDateString(),   // De la fecha (Carbon) a texto: "2027-01-11".
                $ausencia->fecha_fin->toDateString()
            )->get();
        }

        return [
            'ausencias' => $ausencias,
            'afectadas' => $afectadas,
            // El catálogo de motivos es de otro compañero: se lee con una consulta sencilla, sin su modelo.
            'motivosAusencia' => DB::table('motivos_ausencia')->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ];
    }

    // Guarda las casillas marcadas en una tabla de relación (profesor_modulo o profesor_centro):
    // borra las que se han desmarcado y añade las nuevas. Las que siguen marcadas no se tocan.
    private function guardarSeleccion(string $tabla, string $columna, int $profesorId, array $idsMarcados): void
    {
        // Las que había antes.
        $idsAntes = DB::table($tabla)->where('profesor_id', $profesorId)->pluck($columna)->all();

        // Se borran las desmarcadas (estaban antes y ahora no).
        DB::table($tabla)->where('profesor_id', $profesorId)
            ->whereNotIn($columna, $idsMarcados)
            ->delete();

        // Se añaden las nuevas (están ahora y antes no), con los cuatro campos comunes.
        $usuarioRolId = $this->usuarioRolId();
        foreach (array_diff($idsMarcados, $idsAntes) as $id) {
            DB::table($tabla)->insert([
                'profesor_id' => $profesorId,
                $columna => $id,
                'created_at' => now(),
                'updated_at' => now(),
                'persona_id_created' => $usuarioRolId,
            ]);
        }
    }

    // COMPROBAR el formulario. Devuelve: datos de la persona, del profesor, módulos y centros marcados.
    private function validar(Request $request, ?Personas $persona = null): array
    {
        // Documento e IBAN se guardan en mayúsculas y sin espacios.
        $request->merge([
            'numero_documento' => $request->filled('numero_documento')
                ? mb_strtoupper(str_replace(' ', '', $request->input('numero_documento'))) : null,
            'iban' => $request->filled('iban')
                ? mb_strtoupper(str_replace(' ', '', $request->input('iban'))) : null,
        ]);

        // Al editar, el documento no puede tenerlo otra persona.
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
            'especialidad' => ['nullable', 'string', 'max:150'],
            // IBAN: dos letras de país, dos números y el resto letras o números (ES91 2100 0418 4502 0005 1332).
            'iban' => ['nullable', 'string', 'max:34', 'regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{10,30}$/'],
            // Casillas: llegan como una lista de ids (modulos[]=3&modulos[]=5).
            'modulos' => ['nullable', 'array'],
            'modulos.*' => ['integer', 'exists:modulos,id'],
            'centros' => ['nullable', 'array'],
            'centros.*' => ['integer', 'exists:centros,id'],
        ], [
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
            'iban.regex' => 'El IBAN no tiene un formato válido (por ejemplo, ES91 2100 0418 4502 0005 1332).',
        ]);

        $datosPersona = [
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'tipo_documento' => $datos['tipo_documento'] ?? null,
            'numero_documento' => $datos['numero_documento'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'correo_electronico' => $datos['correo_electronico'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
        ];
        $datosProfesor = [
            'especialidad' => $datos['especialidad'] ?? null,
            'iban' => $datos['iban'] ?? null,
            // Desmarcar "Activo" es dar de baja al profesor (no se borra).
            'activo' => $request->boolean('activo'),
        ];

        // array_map('intval', ...) convierte los ids que llegan como texto ("3") en números (3).
        $modulos = array_map('intval', $datos['modulos'] ?? []);
        $centros = array_map('intval', $datos['centros'] ?? []);

        return [$datosPersona, $datosProfesor, $modulos, $centros];
    }

    // Convierte un error de la base de datos en un mensaje para el usuario (igual que en alumnos).
    private function mensajeError(QueryException $e): string
    {
        if ($e->getCode() === '45000') {
            return $e->errorInfo[2] ?? 'La base de datos ha rechazado los datos.';
        }
        if ($e->getCode() === '23000') {
            return 'Ya existe un registro con esos datos (revisa el documento).';
        }

        return 'No se ha podido guardar. Inténtalo de nuevo o avisa al equipo.';
    }
}