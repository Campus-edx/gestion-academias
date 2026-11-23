<?php

namespace App\Http\Controllers\Grupos;

use App\Http\Controllers\Controlador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GruposController extends Controlador
{

    /**
     * Mostrar el listado de grupos.
     */
    public function index()
    {
        $grupos = DB::table('grupos')
            ->join('temporadas', 'grupos.temporada_id', '=', 'temporadas.id')
            ->join('cursos', 'grupos.curso_id', '=', 'cursos.id')
            ->join('centros', 'grupos.centro_id', '=', 'centros.id')
            ->leftJoin('plataformas', 'grupos.plataforma_id', '=', 'plataformas.id')
            ->select(
                'grupos.*',
                'temporadas.nombre as temporada_nombre',
                'temporadas.fecha_inicio',
                'temporadas.fecha_fin',
                'cursos.nombre as curso_nombre',
                'centros.nombre as centro_nombre',
                'plataformas.nombre as plataforma_nombre'
            )
            ->orderBy('grupos.nombre')
            ->get();

        return view('grupos.index', compact('grupos'));
    }
    /**
     * Mostrar formulario de alta de grupos.
     */
    public function create()
    {
        $temporadas = DB::table('temporadas')
            ->where('estado', '!=', 'cerrada')
            ->orderBy('fecha_inicio')
            ->get();

        return view('grupos.formulario', [
            'temporadas' => $temporadas,
            'grupo' => null,
            'modoEdicion' => false,
        ]);
    }

    /**
     * Obtener cursos, centros y plataformas de la empresa
     * correspondiente a la temporada seleccionada.
     */
    public function opciones($temporadaId)
    {
        $temporada = DB::table('temporadas')
            ->where('id', $temporadaId)
            ->first();

        if (!$temporada) {
            abort(404);
        }

        $empresaId = $temporada->empresa_id;

        $cursos = DB::table('cursos')
            ->where('empresa_id', $empresaId)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $centros = DB::table('centros')
            ->where('empresa_id', $empresaId)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $plataformas = DB::table('plataformas')
            ->where('empresa_id', $empresaId)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json([
            'cursos' => $cursos,
            'centros' => $centros,
            'plataformas' => $plataformas,
        ]);
    }

    /**
     * Guardar un grupo nuevo.
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos del formulario.
        $datos = $request->validate([
            'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
            'curso_id' => ['required', 'integer', 'exists:cursos,id'],
            'centro_id' => ['required', 'integer', 'exists:centros,id'],
            'plataforma_id' => ['nullable', 'integer', 'exists:plataformas,id'],
            'nombre' => ['required', 'string', 'max:80'],
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:255'],
            'estado' => [
                'required',
                Rule::in([
                    'planificado',
                    'abierto',
                    'en_curso',
                    'finalizado',
                    'cancelado',
                ]),
            ],
        ], [
            'temporada_id.required' => 'Debes seleccionar una temporada.',
            'curso_id.required' => 'Debes seleccionar un curso.',
            'centro_id.required' => 'Debes seleccionar un centro.',
            'nombre.required' => 'Debes introducir el nombre del grupo.',
            'capacidad_maxima.required' => 'Debes indicar la capacidad máxima.',
            'estado.required' => 'Debes seleccionar un estado.',
        ]);

        // Comprobar que la temporada existe y no está cerrada.
        $temporada = DB::table('temporadas')
            ->where('id', $datos['temporada_id'])
            ->where('estado', '!=', 'cerrada')
            ->first();

        if (!$temporada) {
            return back()
                ->withErrors(['temporada_id' => 'La temporada seleccionada no está disponible.'])
                ->withInput();
        }

        $empresaId = $temporada->empresa_id;

        // Comprobar que el curso pertenece a la empresa y está activo.
        $cursoValido = DB::table('cursos')
            ->where('id', $datos['curso_id'])
            ->where('empresa_id', $empresaId)
            ->where('activo', 1)
            ->exists();

        if (!$cursoValido) {
            return back()
                ->withErrors(['curso_id' => 'El curso seleccionado no pertenece a la empresa de la temporada o no está activo.'])
                ->withInput();
        }

        // Comprobar que el centro pertenece a la empresa y está activo.
        $centroValido = DB::table('centros')
            ->where('id', $datos['centro_id'])
            ->where('empresa_id', $empresaId)
            ->where('activo', 1)
            ->exists();

        if (!$centroValido) {
            return back()
                ->withErrors(['centro_id' => 'El centro seleccionado no pertenece a la empresa de la temporada o no está activo.'])
                ->withInput();
        }

        // Comprobar la plataforma, si se ha seleccionado alguna.
        if (!empty($datos['plataforma_id'])) {
            $plataformaValida = DB::table('plataformas')
                ->where('id', $datos['plataforma_id'])
                ->where('empresa_id', $empresaId)
                ->where('activo', 1)
                ->exists();

            if (!$plataformaValida) {
                return back()
                    ->withErrors(['plataforma_id' => 'La plataforma seleccionada no pertenece a la empresa de la temporada o no está activa.'])
                    ->withInput();
            }
        }

        // Comprobar que no existe otro grupo con el mismo nombre,
        // curso y centro.
        $grupoExistente = DB::table('grupos')
            ->where('curso_id', $datos['curso_id'])
            ->where('centro_id', $datos['centro_id'])
            ->where('nombre', $datos['nombre'])
            ->exists();

        if ($grupoExistente) {
            return back()
                ->withErrors(['nombre' => 'Ya existe un grupo con ese nombre para el curso y centro seleccionados.'])
                ->withInput();
        }

        // Obtener el usuario_rol del usuario autenticado.
        $usuarioRolId = $this->usuarioRolId();

        // Guardar el grupo.
        DB::table('grupos')->insert([
            'temporada_id' => $datos['temporada_id'],
            'curso_id' => $datos['curso_id'],
            'centro_id' => $datos['centro_id'],
            'plataforma_id' => $datos['plataforma_id'] ?? null,
            'nombre' => $datos['nombre'],
            'capacidad_maxima' => $datos['capacidad_maxima'],
            'estado' => $datos['estado'],
            'persona_id_created' => $usuarioRolId,
            'persona_id_updated' => $usuarioRolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('grupos.index')
            ->with('success', 'El grupo se ha creado correctamente.');
    }


    /**
     * Mostrar el formulario para editar un grupo.
     */
    public function edit($id)
    {
        // Buscar el grupo que queremos editar.
        $grupo = DB::table('grupos')
            ->where('id', $id)
            ->first();

        // Si el grupo no existe, mostrar error 404.
        if (!$grupo) {
            abort(404);
        }

        // Cargar las temporadas disponibles.
        // También incluimos la temporada actual del grupo,
        // aunque esté cerrada, para no perder su referencia.
        $temporadas = DB::table('temporadas')
            ->where(function ($consulta) use ($grupo) {
                $consulta->where('estado', '!=', 'cerrada')
                    ->orWhere('id', $grupo->temporada_id);
            })
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        // Mostrar el formulario con los datos del grupo.
        return view('grupos.formulario', [
            'temporadas' => $temporadas,
            'grupo' => $grupo,
            'modoEdicion' => true,
        ]);
    }


    /**
     * Actualizar los datos de un grupo existente.
     */
    public function update(Request $request, $id)
    {
        // Buscar el grupo que queremos modificar.
        $grupo = DB::table('grupos')
            ->where('id', $id)
            ->first();

        if (!$grupo) {
            abort(404);
        }

        // Validar los datos recibidos del formulario.
        $datos = $request->validate([
            'temporada_id' => ['required', 'integer', 'exists:temporadas,id'],
            'curso_id' => ['required', 'integer', 'exists:cursos,id'],
            'centro_id' => ['required', 'integer', 'exists:centros,id'],
            'plataforma_id' => ['nullable', 'integer', 'exists:plataformas,id'],
            'nombre' => ['required', 'string', 'max:80'],
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:255'],
            'estado' => [
                'required',
                Rule::in([
                    'planificado',
                    'abierto',
                    'en_curso',
                    'finalizado',
                    'cancelado',
                ]),
            ],
        ], [
            'temporada_id.required' => 'Debes seleccionar una temporada.',
            'curso_id.required' => 'Debes seleccionar un curso.',
            'centro_id.required' => 'Debes seleccionar un centro.',
            'nombre.required' => 'Debes introducir el nombre del grupo.',
            'capacidad_maxima.required' => 'Debes indicar la capacidad máxima.',
            'estado.required' => 'Debes seleccionar un estado.',
        ]);

        // Comprobar que la temporada existe.
        $temporada = DB::table('temporadas')
            ->where('id', $datos['temporada_id'])
            ->first();

        if (!$temporada) {
            return back()
                ->withErrors(['temporada_id' => 'La temporada seleccionada no existe.'])
                ->withInput();
        }

        // No permitir cambiar a una temporada cerrada.
        // Sí permitimos conservar la temporada actual aunque esté cerrada.
        if (
            $temporada->estado === 'cerrada'
            && (int) $datos['temporada_id'] !== (int) $grupo->temporada_id
        ) {
            return back()
                ->withErrors(['temporada_id' => 'No puedes asignar el grupo a una temporada cerrada.'])
                ->withInput();
        }

        $empresaId = $temporada->empresa_id;

        // Comprobar que el curso pertenece a la empresa y está activo.
        $cursoValido = DB::table('cursos')
            ->where('id', $datos['curso_id'])
            ->where('empresa_id', $empresaId)
            ->where('activo', 1)
            ->exists();

        if (!$cursoValido) {
            return back()
                ->withErrors(['curso_id' => 'El curso seleccionado no pertenece a la empresa de la temporada o no está activo.'])
                ->withInput();
        }

        // Comprobar que el centro pertenece a la empresa y está activo.
        $centroValido = DB::table('centros')
            ->where('id', $datos['centro_id'])
            ->where('empresa_id', $empresaId)
            ->where('activo', 1)
            ->exists();

        if (!$centroValido) {
            return back()
                ->withErrors(['centro_id' => 'El centro seleccionado no pertenece a la empresa de la temporada o no está activo.'])
                ->withInput();
        }

        // Comprobar la plataforma, si se ha seleccionado alguna.
        if (!empty($datos['plataforma_id'])) {
            $plataformaValida = DB::table('plataformas')
                ->where('id', $datos['plataforma_id'])
                ->where('empresa_id', $empresaId)
                ->where('activo', 1)
                ->exists();

            if (!$plataformaValida) {
                return back()
                    ->withErrors(['plataforma_id' => 'La plataforma seleccionada no pertenece a la empresa de la temporada o no está activa.'])
                    ->withInput();
            }
        }

        // Comprobar que no exista otro grupo con el mismo nombre,
        // curso y centro, excluyendo el grupo que estamos editando.
        $grupoExistente = DB::table('grupos')
            ->where('curso_id', $datos['curso_id'])
            ->where('centro_id', $datos['centro_id'])
            ->where('nombre', $datos['nombre'])
            ->where('id', '!=', $id)
            ->exists();

        if ($grupoExistente) {
            return back()
                ->withErrors(['nombre' => 'Ya existe otro grupo con ese nombre para el curso y centro seleccionados.'])
                ->withInput();
        }

        // Obtener el usuario_rol del usuario autenticado.
        $usuarioRolId = $this->usuarioRolId();

        // Actualizar el grupo.
        DB::table('grupos')
            ->where('id', $id)
            ->update([
                'temporada_id' => $datos['temporada_id'],
                'curso_id' => $datos['curso_id'],
                'centro_id' => $datos['centro_id'],
                'plataforma_id' => $datos['plataforma_id'] ?? null,
                'nombre' => $datos['nombre'],
                'capacidad_maxima' => $datos['capacidad_maxima'],
                'estado' => $datos['estado'],
                'persona_id_updated' => $usuarioRolId,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('grupos.index')
            ->with('success', 'El grupo se ha actualizado correctamente.');
    }
}
