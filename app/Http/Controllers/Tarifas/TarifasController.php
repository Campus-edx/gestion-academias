<?php


// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers\Tarifas;

// Modelos que usa esta pantalla.
use App\Models\Tarifas\Tarifas;
use App\Http\Controllers\Controlador;
// Piezas de Laravel.
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


// Controlador de TARIFAS (Jira: SCRUM-354 Gestionar tarifas: 356 alta y 357 periodicidad).
// Solo las tarifas de los cursos de la empresa del usuario.
// No hay "modificar": si cambia el precio, se cierra la tarifa (vigente_hasta) y se crea otra.
// Permisos: ver con 'facturas.ver'; crear y cerrar con 'facturas.emitir'
// (son los permisos de facturación que ya hay; el equipo puede crear unos propios más adelante).
class TarifasController extends Controlador
{
    // LISTADO (ruta GET /tarifas), con filtros por curso y por estado.
    public function index(Request $request): View
    {
        $this->authorize('facturas.ver');

        $cursoId = $request->query('curso_id');
        // Por defecto, solo las vigentes hoy. "todas" enseña también las cerradas y las futuras.
        $estado = $request->query('estado', 'vigentes') === 'todas' ? 'todas' : 'vigentes';

        $tarifas = $this->tarifasDeMiEmpresa($request)
            ->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            // vigentesEn() es el scope del modelo Tarifas (sin fecha = hoy).
            ->when($estado === 'vigentes', fn ($q) => $q->vigentesEn())
            ->orderBy('c.nombre')
            ->orderByRaw('tarifas.curso_modulo_id IS NOT NULL') // Primero las del curso completo.
            ->orderBy('m.nombre')
            ->orderByDesc('tarifas.vigente_desde')
            ->get();

        return view('tarifas.index', [
            'tarifas' => $tarifas,
            'cursos' => $this->misCursos($request),
            'cursoId' => $cursoId,
            'estado' => $estado,
        ]);
    }

    // FORMULARIO DE ALTA (ruta GET /tarifas/crear).
    public function create(Request $request): View
    {
        $this->authorize('facturas.emitir');

        return view('tarifas.formulario', [
            'destinos' => $this->destinos($request),
            'periodicidades' => Tarifas::PERIODICIDADES,
        ]);
    }

    // GUARDAR UNA TARIFA NUEVA (ruta POST /tarifas).
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('facturas.emitir');

        $datos = $request->validate([
            // "destino" dice de qué es la tarifa: "curso:5" (curso completo) o "modulo:12" (un curso_modulo).
            'destino' => ['required', Rule::in(array_keys($this->destinos($request)))],
            'nombre' => ['required', 'string', 'max:120'],
            // numeric = número (con punto para los decimales: 45.50).
            'importe' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'periodicidad' => ['required', Rule::in(array_keys(Tarifas::PERIODICIDADES))],
            'iva_porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'vigente_desde' => ['required', 'date'],
            'vigente_hasta' => ['nullable', 'date', 'after_or_equal:vigente_desde'],
        ], [
            'destino.required' => 'Elige el curso o el módulo.',
            'destino.in' => 'Ese curso o módulo no es de tu empresa.',
            'nombre.required' => 'Escribe un nombre para la tarifa.',
            'importe.required' => 'Escribe el importe.',
            'importe.numeric' => 'El importe tiene que ser un número (por ejemplo, 45.50).',
            'iva_porcentaje.required' => 'Escribe el IVA (0 si no lleva).',
            'iva_porcentaje.max' => 'El IVA no puede pasar de 100.',
            'vigente_desde.required' => 'Indica desde qué fecha vale.',
            'vigente_hasta.after_or_equal' => 'La fecha "hasta" no puede ser anterior a "desde".',
        ]);

        // De "curso:5" a ['curso', '5'].
        [$tipo, $id] = explode(':', $datos['destino']);
        $columna = $tipo === 'curso' ? 'curso_id' : 'curso_modulo_id';
        $hasta = $datos['vigente_hasta'] ?? null;

        // Regla que no comprueba la base de datos: para el mismo curso (o módulo) y la misma
        // periodicidad no puede haber dos tarifas vigentes a la vez (no se sabría cuál cobrar).
        // Sí puede haber a la vez una mensual y una de pago único: son dos formas de pagar.
        $pisaOtra = Tarifas::query()
            ->where($columna, $id)
            ->where('periodicidad', $datos['periodicidad'])
            ->where(fn ($q) => $q->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', $datos['vigente_desde']))
            ->when($hasta, fn ($q) => $q->where('vigente_desde', '<=', $hasta))
            ->exists();

        if ($pisaOtra) {
            return back()->withInput()->withErrors([
                'vigente_desde' => 'Ya hay una tarifa ' . mb_strtolower(Tarifas::PERIODICIDADES[$datos['periodicidad']])
                    . ' de ese curso o módulo en esas fechas. Ciérrala primero (para cambiar un precio se cierra la actual y se crea otra).',
            ]);
        }

        Tarifas::create([
            // Solo se rellena una de las dos columnas: la otra queda vacía (null).
            'curso_id' => $tipo === 'curso' ? $id : null,
            'curso_modulo_id' => $tipo === 'modulo' ? $id : null,
            'nombre' => $datos['nombre'],
            'importe' => $datos['importe'],
            'periodicidad' => $datos['periodicidad'],
            'iva_porcentaje' => $datos['iva_porcentaje'],
            'vigente_desde' => $datos['vigente_desde'],
            'vigente_hasta' => $hasta,
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('tarifas.index')->with('success', 'Tarifa creada.');
    }

    // CERRAR una tarifa poniendo su fecha "hasta" (ruta PUT /tarifas/{tarifa}/cerrar).
    public function cerrar(Request $request, Tarifas $tarifa): RedirectResponse
    {
        $this->authorize('facturas.emitir');

        // Seguridad: la tarifa tiene que ser de un curso de mi empresa.
        abort_unless($this->tarifasDeMiEmpresa($request)->where('tarifas.id', $tarifa->id)->exists(), 404);

        $datos = $request->validate([
            'vigente_hasta' => ['required', 'date', 'after_or_equal:' . $tarifa->vigente_desde->toDateString()],
        ], [
            'vigente_hasta.required' => 'Indica hasta qué día vale.',
            'vigente_hasta.after_or_equal' => 'La fecha "hasta" no puede ser anterior a "desde".',
        ]);

        $tarifa->update([
            'vigente_hasta' => $datos['vigente_hasta'],
            'persona_id_updated' => $this->usuarioRolId(),
        ]);

        return redirect()->route('tarifas.index', ['estado' => 'todas'])->with('success', 'Tarifa cerrada.');
    }

    // ─── Métodos privados ───

    // Las tarifas de los cursos de mi empresa, con el nombre del curso y del módulo.
    // Una tarifa de módulo no tiene curso_id: su curso se saca del curso_modulo.
    // COALESCE(a, b) = "a si no está vacío; si no, b".
    private function tarifasDeMiEmpresa(Request $request): Builder
    {
        return Tarifas::query()
            ->leftJoin('curso_modulo as cm', 'cm.id', '=', 'tarifas.curso_modulo_id')
            ->leftJoin('modulos as m', 'm.id', '=', 'cm.modulo_id')
            ->join('cursos as c', fn ($union) => $union->on('c.id', '=', DB::raw('COALESCE(tarifas.curso_id, cm.curso_id)')))
            ->where('c.empresa_id', $request->user()->empresa_id)
            ->select('tarifas.*', 'c.nombre as curso', 'm.nombre as modulo');
    }

    // Los cursos de mi empresa (para el filtro). Son de la Persona 2: consulta sencilla, sin su modelo.
    private function misCursos(Request $request)
    {
        return DB::table('cursos')
            ->where('empresa_id', $request->user()->empresa_id)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    // Las opciones del desplegable "Tarifa de": cada curso completo y cada módulo de cada curso.
    // Devuelve ['curso:5' => 'Inglés B1 · curso completo', 'modulo:12' => 'Inglés B1 · módulo Speaking', ...].
    // También sirve para validar: solo vale una de estas claves (Rule::in).
    private function destinos(Request $request): array
    {
        $destinos = [];

        foreach ($this->misCursos($request) as $curso) {
            $destinos['curso:' . $curso->id] = $curso->nombre . ' · curso completo';

            $modulos = DB::table('curso_modulo as cm')
                ->join('modulos as m', 'm.id', '=', 'cm.modulo_id')
                ->where('cm.curso_id', $curso->id)
                ->orderBy('cm.orden')
                ->get(['cm.id', 'm.nombre']);

            foreach ($modulos as $modulo) {
                $destinos['modulo:' . $modulo->id] = $curso->nombre . ' · módulo ' . $modulo->nombre;
            }
        }

        return $destinos;
    }
}