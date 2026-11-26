<?php

// Dirección del archivo: carpeta app/Http/Controllers/Inscripciones.
namespace App\Http\Controllers\Inscripciones;

// La clase base de todos los controladores está en la carpeta de arriba: hay que traerla con "use".
use App\Http\Controllers\Controlador;
// Modelos que usa. Inscripciones está en su carpeta; Alumnos y Cursos siguen en app/Models.
use App\Models\Alumnos;
use App\Models\Cursos;
use App\Models\Inscripciones\Inscripciones;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Controlador de INSCRIPCIONES (matrículas).
// Jira: SCRUM-273 Inscribir alumnos, SCRUM-278 Gestionar bajas,
//       SCRUM-282 Consultar historial de inscripciones y SCRUM-220 Responsables de pago.
//       Tarifa y descuento (SCRUM-358, con la Persona 1): de momento son OPCIONALES, porque las tarifas
//       aún no tienen pantalla. Si se elige una, se comprueba que es del curso y vale en la fecha de alta.
//       Cuando la Persona 1 suba sus tarifas, se puede volver obligatoria (cambiar 'nullable' por 'required').
// Se matricula desde la ficha del alumno (botón "Matricular"); el listado es el historial completo.
// En la ficha, cada inscripción tiene sus botones:
//   · Activa → "Dar de baja" (el alumno se va) y "Anular" (la matrícula fue un error).
//   · Baja   → "Reactivar" (el alumno VUELVE: inscripción nueva), "Deshacer baja" (la baja fue un
//              ERROR: la misma vuelve a activa) y "Anular".
// Los errores de estos botones van todos en el mismo saco ("inscripciones") y se enseñan en la tarjeta.
class InscripcionesController extends Controlador
{
    // HISTORIAL de inscripciones con filtros (ruta GET /inscripciones). SCRUM-282.
    public function index(Request $request): View
    {
        $estado = $request->query('estado', 'activas');   // activas, anteriores, anuladas o todas.
        $cursoId = $request->query('curso_id');
        $buscar = trim((string) $request->query('buscar', ''));

        $inscripciones = Inscripciones::historial()
            // Cada empresa ve las inscripciones de sus centros.
            ->where('e.id', $request->user()->empresa_id)
            // Activas = estado activa; anteriores = dadas de baja o finalizadas; anuladas = hechas por error.
            ->when($estado === 'activas', fn ($q) => $q->where('i.estado', 'activa'))
            ->when($estado === 'anteriores', fn ($q) => $q->whereIn('i.estado', ['baja', 'finalizada']))
            ->when($estado === 'anuladas', fn ($q) => $q->where('i.estado', 'anulada'))
            ->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($donde) use ($buscar) {
                    $donde->where('p.nombre', 'like', "%{$buscar}%")
                        ->orWhere('p.apellidos', 'like', "%{$buscar}%");
                });
            })
            ->get();

        return view('inscripciones.index', [
            'inscripciones' => $inscripciones,
            'estado' => $estado,
            'cursoId' => $cursoId,
            'buscar' => $buscar,
            'cursos' => Cursos::query()->where('empresa_id', $request->user()->empresa_id)->orderBy('nombre')->get(),
        ]);
    }

    // FORMULARIO para matricular a un alumno (ruta GET /inscripciones/crear?alumno_id=5).
    // Se llega desde la ficha del alumno: hace falta saber el alumno para ofrecer sus posibles pagadores.
    public function create(Request $request): View|RedirectResponse
    {
        $alumno = Alumnos::with('persona')->find($request->query('alumno_id'));

        if (!$alumno) {
            return redirect()->route('alumnos.index')
                ->with('warning', 'Para matricular, abre la ficha del alumno y pulsa "Matricular".');
        }

        return view('inscripciones.formulario', [
            'alumno' => $alumno,
            'grupos' => $this->gruposDisponibles($request),
            'pagadores' => $this->posiblesPagadores($alumno),
            // NUEVO (SCRUM-358): tarifas de los cursos de la empresa y descuentos activos.
            'tarifas' => $this->tarifasDisponibles($request),
            'descuentos' => $this->descuentosActivos($request),
        ]);
    }

    // GUARDAR la matrícula (ruta POST /inscripciones).
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'alumno_id' => ['required', 'integer', 'exists:alumnos,id'],
            'grupo_id' => ['required', 'integer', 'exists:grupos,id'],
            'pagador_persona_id' => ['required', 'integer'],
            'fecha_alta' => ['required', 'date'],
            // Tarifa y descuento: de momento opcionales. Que sean del curso y de la empresa se comprueba más abajo.
            'tarifa_id' => ['nullable', 'integer'],
            'descuento_id' => ['nullable', 'integer'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            'grupo_id.required' => 'Elige el grupo.',
            'pagador_persona_id.required' => 'Elige quién paga.',
            'fecha_alta.required' => 'Indica la fecha de alta.',
        ]);

        $alumno = Alumnos::findOrFail($datos['alumno_id']);

        // El grupo tiene que estar en los que se ofrecen (de la empresa del usuario y admitiendo alumnos).
        $grupo = $this->gruposDisponibles($request)->firstWhere('id', (int) $datos['grupo_id']);
        if (!$grupo) {
            return back()->withInput()->withErrors(['grupo_id' => 'Ese grupo no admite matrículas (está finalizado, cancelado o es de otra empresa).']);
        }

        // Plazas: la base de datos no lo comprueba, así que se mira aquí con la vista v_grupos_plazas.
        if ($grupo->plazas_libres <= 0) {
            return back()->withInput()->withErrors(['grupo_id' => 'El grupo ' . $grupo->grupo . ' no tiene plazas libres.']);
        }

        // La fecha de alta no puede ser posterior al final del grupo.
        if ($datos['fecha_alta'] > $grupo->fecha_fin) {
            return back()->withInput()->withErrors(['fecha_alta' => 'La fecha de alta es posterior al final del grupo.']);
        }

        // SCRUM-220: quien paga tiene que ser el propio alumno o uno de sus tutores.
        $idsPagadores = $this->posiblesPagadores($alumno)->pluck('id')->all();
        if (!in_array((int) $datos['pagador_persona_id'], $idsPagadores, true)) {
            return back()->withInput()->withErrors(['pagador_persona_id' => 'Quien paga tiene que ser el alumno o uno de sus tutores.']);
        }

        // Tarifa (si se elige): tiene que ser DEL CURSO DEL GRUPO y valer en la fecha de alta.
        $tarifa = null;
        if (!empty($datos['tarifa_id'])) {
            $tarifa = $this->tarifasDisponibles($request)->firstWhere('id', (int) $datos['tarifa_id']);
            if (!$tarifa || (int) $tarifa->curso_id !== (int) $grupo->curso_id) {
                return back()->withInput()->withErrors(['tarifa_id' => 'Esa tarifa no es del curso de ' . $grupo->grupo . '.']);
            }
            // Vigente en la fecha de alta: ya empezada y sin terminar (las fechas como texto "2026-11-19" se comparan bien).
            if ($tarifa->vigente_desde > $datos['fecha_alta'] || ($tarifa->vigente_hasta && $tarifa->vigente_hasta < $datos['fecha_alta'])) {
                return back()->withInput()->withErrors(['tarifa_id' => 'Esa tarifa no vale en la fecha de alta (mira sus fechas).']);
            }
        }

        // El descuento (si se elige) tiene que ser de la empresa y estar activo.
        if (!empty($datos['descuento_id']) && !$this->descuentosActivos($request)->contains('id', (int) $datos['descuento_id'])) {
            return back()->withInput()->withErrors(['descuento_id' => 'Ese descuento no existe o está desactivado.']);
        }

        // Un alumno solo puede tener UNA inscripción ACTIVA en el mismo grupo (la base de datos tampoco lo deja).
        // Si se dio de baja, sí puede volver: se usa el botón "Reactivar" de su ficha.
        if (Inscripciones::query()->where('alumno_id', $alumno->id)->where('grupo_id', $grupo->id)->where('estado', 'activa')->exists()) {
            return back()->withInput()->withErrors(['grupo_id' => 'Este alumno ya está matriculado en ese grupo.']);
        }

        try {
            Inscripciones::create([
                'alumno_id' => $alumno->id,
                'grupo_id' => $grupo->id,
                'pagador_persona_id' => $datos['pagador_persona_id'],
                'tarifa_id' => $tarifa?->id,                        // Vacío si no se eligió tarifa.
                'descuento_id' => $datos['descuento_id'] ?? null,
                'fecha_alta' => $datos['fecha_alta'],
                'estado' => 'activa',
                'observaciones' => $datos['observaciones'] ?? null,
                'persona_id_created' => $this->usuarioRolId(),
            ]);
        } catch (QueryException $e) {
            // Si con esta matrícula los alumnos ya no caben en el aula, la base de datos la rechaza
            // (código 45000) con un mensaje en castellano: se enseña tal cual.
            if ($e->getCode() === '45000') {
                return back()->withInput()->withErrors(['grupo_id' => $e->errorInfo[2] ?? 'La base de datos ha rechazado la matrícula.']);
            }
            throw $e;
        }

        return redirect()->route('alumnos.edit', $alumno)->with('success', 'Alumno matriculado en ' . $grupo->curso . ' · ' . $grupo->grupo . '.');
    }

    // DAR DE BAJA una inscripción (ruta PUT /inscripciones/{inscripcion}/baja). SCRUM-278.
    // No se borra: se pone la fecha de baja y el estado "baja". La plaza queda libre sola.
    public function baja(Request $request, Inscripciones $inscripcion): RedirectResponse
    {
        // Solo se dan de baja inscripciones activas y de un grupo de la empresa del usuario.
        abort_if($inscripcion->estado !== 'activa', 404);
        $this->comprobarEmpresa($request, $inscripcion);

        // validateWithBag: si falla, el error va al saco "inscripciones" (se enseña en la tarjeta de la ficha).
        $datos = $request->validateWithBag('inscripciones', [
            'fecha_baja' => ['required', 'date', 'after_or_equal:' . $inscripcion->fecha_alta->toDateString()],
        ], [
            'fecha_baja.required' => 'Indica la fecha de baja.',
            'fecha_baja.after_or_equal' => 'La fecha de baja no puede ser anterior a la de alta.',
        ]);

        return $this->guardar($inscripcion, [
            'fecha_baja' => $datos['fecha_baja'],
            'estado' => 'baja',
        ], 'Inscripción dada de baja.');
    }

    // REACTIVAR: el alumno se dio de baja y VUELVE al mismo grupo (ruta POST /inscripciones/{inscripcion}/reactivar).
    // No se toca la inscripción de baja (se queda como historial): se crea una NUEVA con el mismo grupo,
    // el mismo pagador, la misma tarifa (si sigue valiendo) y el mismo descuento, desde la fecha de vuelta.
    public function reactivar(Request $request, Inscripciones $inscripcion): RedirectResponse
    {
        // Solo las de baja (las finalizadas no: el grupo ya terminó) y de un grupo de la empresa del usuario.
        abort_if($inscripcion->estado !== 'baja', 404);
        $this->comprobarEmpresa($request, $inscripcion);

        $datos = $request->validateWithBag('inscripciones', [
            'fecha_alta' => ['required', 'date', 'after_or_equal:' . $inscripcion->fecha_baja->toDateString()],
        ], [
            'fecha_alta.required' => 'Indica la fecha de vuelta.',
            'fecha_alta.after_or_equal' => 'La fecha de vuelta no puede ser anterior a la de baja (' . $inscripcion->fecha_baja->format('d/m/Y') . ').',
        ]);

        // Solo desde la ÚLTIMA inscripción de ese alumno en ese grupo (si ya volvió, no se vuelve otra vez desde una vieja).
        if (!$this->esLaUltima($inscripcion)) {
            return $this->volverConError($inscripcion, 'Este alumno ya ha vuelto a ese grupo después de esta baja.');
        }

        // El grupo tiene que seguir admitiendo alumnos y tener plazas.
        $grupo = $this->gruposDisponibles($request)->firstWhere('id', (int) $inscripcion->grupo_id);
        if (!$grupo) {
            return $this->volverConError($inscripcion, 'El grupo ya no admite matrículas (está finalizado o cancelado).');
        }
        if ($grupo->plazas_libres <= 0) {
            return $this->volverConError($inscripcion, 'El grupo ' . $grupo->grupo . ' no tiene plazas libres.');
        }
        if ($datos['fecha_alta'] > $grupo->fecha_fin) {
            return $this->volverConError($inscripcion, 'La fecha de vuelta es posterior al final del grupo.');
        }

        // Quien pagaba tiene que poder seguir pagando (por si se ha quitado el tutor del alumno).
        $alumno = Alumnos::findOrFail($inscripcion->alumno_id);
        if (!$this->posiblesPagadores($alumno)->contains('id', (int) $inscripcion->pagador_persona_id)) {
            return $this->volverConError($inscripcion, 'Quien pagaba ya no es el alumno ni uno de sus tutores: matricúlalo con el botón "Matricular".');
        }

        // La tarifa solo se copia si sigue valiendo en la fecha de vuelta; si no, se queda vacía.
        $tarifaId = null;
        if ($inscripcion->tarifa_id) {
            $tarifa = $this->tarifasDisponibles($request)->firstWhere('id', (int) $inscripcion->tarifa_id);
            if ($tarifa && $tarifa->vigente_desde <= $datos['fecha_alta']
                && (!$tarifa->vigente_hasta || $tarifa->vigente_hasta >= $datos['fecha_alta'])) {
                $tarifaId = $tarifa->id;
            }
        }
        // El descuento, igual: solo si sigue activo.
        $descuentoId = $inscripcion->descuento_id && $this->descuentosActivos($request)->contains('id', (int) $inscripcion->descuento_id)
            ? $inscripcion->descuento_id
            : null;

        try {
            Inscripciones::create([
                'alumno_id' => $inscripcion->alumno_id,
                'grupo_id' => $inscripcion->grupo_id,
                'pagador_persona_id' => $inscripcion->pagador_persona_id,
                'tarifa_id' => $tarifaId,
                'descuento_id' => $descuentoId,
                'fecha_alta' => $datos['fecha_alta'],
                'estado' => 'activa',
                'observaciones' => 'Vuelve tras la baja del ' . $inscripcion->fecha_baja->format('d/m/Y') . '.',
                'persona_id_created' => $this->usuarioRolId(),
            ]);
        } catch (QueryException $e) {
            // La base de datos la rechaza si los periodos se pisan o si no caben en el aula (código 45000).
            if ($e->getCode() === '45000') {
                return $this->volverConError($inscripcion, $e->errorInfo[2] ?? 'La base de datos ha rechazado la matrícula.');
            }
            throw $e;
        }

        $aviso = 'Alumno matriculado otra vez en ' . $grupo->curso . ' · ' . $grupo->grupo . '.';
        if ($inscripcion->tarifa_id && !$tarifaId) {
            $aviso .= ' Su tarifa anterior ya no vale: queda sin tarifa.';
        }

        return redirect()->route('alumnos.edit', $inscripcion->alumno_id)->with('success', $aviso);
    }

    // DESHACER BAJA: la baja fue un ERROR (ruta PUT /inscripciones/{inscripcion}/deshacer-baja).
    // La MISMA inscripción vuelve a estar activa y se borra su fecha de baja, como si no se hubiera dado de baja.
    // (Si el alumno de verdad se fue y vuelve, se usa "Reactivar", que deja la baja en el historial.)
    public function deshacerBaja(Request $request, Inscripciones $inscripcion): RedirectResponse
    {
        abort_if($inscripcion->estado !== 'baja', 404);
        $this->comprobarEmpresa($request, $inscripcion);

        // Solo la última: si después de esta baja el alumno ya volvió, deshacerla haría que se pisaran.
        if (!$this->esLaUltima($inscripcion)) {
            return $this->volverConError($inscripcion, 'No se puede deshacer: el alumno ya ha vuelto a ese grupo después de esta baja.');
        }

        // El grupo tiene que seguir abierto y con plaza (mientras estaba de baja, otro alumno pudo ocupar su sitio).
        $grupo = $this->gruposDisponibles($request)->firstWhere('id', (int) $inscripcion->grupo_id);
        if (!$grupo) {
            return $this->volverConError($inscripcion, 'No se puede deshacer: el grupo ya no admite matrículas (está finalizado o cancelado).');
        }
        if ($grupo->plazas_libres <= 0) {
            return $this->volverConError($inscripcion, 'No se puede deshacer: el grupo ' . $grupo->grupo . ' ya no tiene plazas libres.');
        }

        return $this->guardar($inscripcion, [
            'fecha_baja' => null,
            'estado' => 'activa',
        ], 'Baja deshecha: la inscripción vuelve a estar activa.');
    }

    // ANULAR: la matrícula fue un ERROR (grupo, pagador o fechas equivocados). Ruta PUT /inscripciones/{inscripcion}/anular.
    // No se borra: queda con estado "anulada" y el motivo en observaciones. No cuenta para nada
    // (plazas, aforo, facturas) y se puede matricular bien al alumno después.
    public function anular(Request $request, Inscripciones $inscripcion): RedirectResponse
    {
        // Se pueden anular las activas y las de baja (no las finalizadas ni las ya anuladas).
        abort_unless(in_array($inscripcion->estado, ['activa', 'baja'], true), 404);
        $this->comprobarEmpresa($request, $inscripcion);

        $datos = $request->validateWithBag('inscripciones', [
            'motivo' => ['required', 'string', 'max:200'],
        ], [
            'motivo.required' => 'Para anular una matrícula, escribe el motivo (por ejemplo: "grupo equivocado").',
        ]);

        // Si ya se ha facturado, no se puede anular (antes hay que hacer una factura rectificativa).
        // La base de datos también lo impide; aquí se mira antes para dar un mensaje claro.
        if (DB::table('lineas_factura')->where('inscripcion_id', $inscripcion->id)->exists()) {
            return $this->volverConError($inscripcion, 'No se puede anular: la matrícula ya está facturada (habla con facturación para hacer una rectificativa).');
        }

        // El motivo se añade al final de las observaciones, con la fecha de hoy.
        $nota = 'Anulada el ' . now()->format('d/m/Y') . ': ' . $datos['motivo'];

        return $this->guardar($inscripcion, [
            'estado' => 'anulada',
            'observaciones' => trim(($inscripcion->observaciones ?? '') . "\n" . $nota),
        ], 'Matrícula anulada.');
    }

    // ── Ayudas de los botones de la ficha ──────────────────────────────

    // La inscripción tiene que ser de un grupo de la empresa del usuario (si no, "no existe": error 404).
    private function comprobarEmpresa(Request $request, Inscripciones $inscripcion): void
    {
        abort_unless(
            Inscripciones::historial()->where('i.id', $inscripcion->id)->where('e.id', $request->user()->empresa_id)->exists(),
            404
        );
    }

    // ¿Es la última inscripción (sin contar las anuladas) de ese alumno en ese grupo?
    private function esLaUltima(Inscripciones $inscripcion): bool
    {
        return !Inscripciones::query()
            ->where('alumno_id', $inscripcion->alumno_id)
            ->where('grupo_id', $inscripcion->grupo_id)
            ->where('estado', '<>', 'anulada')
            ->where('id', '>', $inscripcion->id)
            ->exists();
    }

    // Vuelve a la ficha del alumno con un mensaje de error en la tarjeta de inscripciones.
    private function volverConError(Inscripciones $inscripcion, string $mensaje): RedirectResponse
    {
        return redirect()->route('alumnos.edit', $inscripcion->alumno_id)->withErrors(['inscripcion' => $mensaje], 'inscripciones');
    }

    // Guarda los cambios de una inscripción y vuelve a la ficha con un aviso.
    // Si la base de datos los rechaza (periodos que se pisan, aula sin sitio, ya facturada: código 45000),
    // enseña su mensaje en vez de dar un error 500.
    private function guardar(Inscripciones $inscripcion, array $cambios, string $aviso): RedirectResponse
    {
        try {
            $inscripcion->update($cambios + ['persona_id_updated' => $this->usuarioRolId()]);
        } catch (QueryException $e) {
            if ($e->getCode() === '45000') {
                return $this->volverConError($inscripcion, $e->errorInfo[2] ?? 'La base de datos ha rechazado el cambio.');
            }
            throw $e;
        }

        return redirect()->route('alumnos.edit', $inscripcion->alumno_id)->with('success', $aviso);
    }

    // Grupos en los que se puede matricular: de los centros de la empresa del usuario,
    // que no estén finalizados ni cancelados, con sus plazas libres (vista v_grupos_plazas).
    private function gruposDisponibles(Request $request)
    {
        return DB::table('grupos as g')
            ->join('cursos as c', 'c.id', '=', 'g.curso_id')
            ->join('centros as ce', 'ce.id', '=', 'g.centro_id')
            ->join('v_grupos_plazas as vp', 'vp.id', '=', 'g.id')
            ->where('ce.empresa_id', $request->user()->empresa_id)
            ->whereIn('g.estado', ['planificado', 'abierto', 'en_curso'])
            ->orderBy('c.nombre')->orderBy('g.nombre')
            ->get([
                'g.id', 'g.nombre as grupo', 'g.fecha_inicio', 'g.fecha_fin',
                'g.curso_id',   // NUEVO (SCRUM-358): para comprobar que la tarifa es de este curso.
                'c.nombre as curso', 'ce.nombre as centro', 'vp.plazas_libres',
            ]);
    }

    // NUEVO (SCRUM-358): tarifas de CURSO COMPLETO de los cursos de la empresa que no están ya cerradas
    // (vigentes hoy o que empiezan más adelante). Las tarifas son de la Persona 1: se leen con una
    // consulta sencilla, sin su modelo. (Las de módulos sueltos van en inscripcion_modulo, no aquí.)
    private function tarifasDisponibles(Request $request)
    {
        $hoy = now()->toDateString();

        return DB::table('tarifas as t')
            ->join('cursos as c', 'c.id', '=', 't.curso_id')
            ->where('c.empresa_id', $request->user()->empresa_id)
            ->where(fn ($q) => $q->whereNull('t.vigente_hasta')->orWhere('t.vigente_hasta', '>=', $hoy))
            ->orderBy('c.nombre')->orderBy('t.vigente_desde')
            ->get(['t.id', 't.curso_id', 'c.nombre as curso', 't.nombre', 't.importe', 't.periodicidad',
                't.iva_porcentaje', 't.vigente_desde', 't.vigente_hasta']);
    }

    // NUEVO (SCRUM-358): descuentos activos de la empresa (también de la Persona 1).
    private function descuentosActivos(Request $request)
    {
        return DB::table('descuentos')
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'tipo', 'valor']);
    }

    // SCRUM-220: quién puede pagar la matrícula de un alumno: él mismo y sus tutores.
    // Devuelve una lista de personas (id, nombre y una etiqueta: "el propio alumno" o el parentesco).
    private function posiblesPagadores(Alumnos $alumno)
    {
        $elAlumno = DB::table('personas as p')
            ->where('p.id', $alumno->persona_id)
            ->select('p.id', 'p.nombre', 'p.apellidos', DB::raw("'el propio alumno' as relacion"));

        $susTutores = DB::table('tutor_alumno as ta')
            ->join('tutores as t', 't.id', '=', 'ta.tutor_id')
            ->join('personas as p', 'p.id', '=', 't.persona_id')
            ->join('parentescos as pa', 'pa.id', '=', 'ta.parentesco_id')
            ->where('ta.alumno_id', $alumno->id)
            ->select('p.id', 'p.nombre', 'p.apellidos', 'pa.nombre as relacion');

        // union: junta las dos listas en una.
        return $elAlumno->union($susTutores)->get();
    }
}