<?php

namespace App\Services;

use App\Models\Usuarios;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GruposService
{

    //.................................. usuarioActual ....................................................
    private function usuarioActual(): Usuarios
    {
        $usuario = Auth::user();

        if (!$usuario instanceof Usuarios) {
            throw new AuthorizationException(
                'Debes iniciar sesión para acceder a los grupos.'
            );
        }

        return $usuario;
    }

    //.................................. tienePermisoGlobal ....................................................
    private function tienePermisoGlobal(
        Usuarios $usuario,
        string $permiso
    ): bool {
        return DB::table('v_usuarios_permisos')
            ->where('usuario_id', $usuario->id)
            ->where('permiso', $permiso)
            ->whereNull('centro_id')
            ->exists();
    }


    //.................................. tienePermisoCentro ....................................................
    private function tienePermisoCentro(
        Usuarios $usuario,
        string $permiso,
        int $centroId
    ): bool {
        return DB::table('v_usuarios_permisos')
            ->where('usuario_id', $usuario->id)
            ->where('permiso', $permiso)
            ->where(function ($consulta) use ($centroId) {
                $consulta->whereNull('centro_id')
                    ->orWhere('centro_id', $centroId);
            })
            ->exists();
    }


    //.................................. esAdministradorGeneral ....................................................
    private function esAdministradorGeneral(
        Usuarios $usuario,
        string $permiso
    ): bool {
        return DB::table('usuario_rol as ur')
            ->join('roles as r', 'r.id', '=', 'ur.rol_id')
            ->join('rol_permiso as rp', 'rp.rol_id', '=', 'r.id')
            ->join('permisos as p', 'p.id', '=', 'rp.permiso_id')
            ->where('ur.usuario_id', $usuario->id)
            ->where('r.codigo', 'administrador_general')
            ->where('p.codigo', $permiso)
            ->where('ur.fecha_inicio', '<=', now()->toDateString())
            ->where(function ($vigencia) {
                $vigencia->whereNull('ur.fecha_fin')
                    ->orWhere('ur.fecha_fin', '>=', now()->toDateString());
            })
            ->exists();
    }


    //.................................. tieneAlgunCentroEditable ....................................................
    private function tieneAlgunCentroEditable(
        Usuarios $usuario
    ): bool {
        if ($this->tienePermisoGlobal($usuario, 'grupos.editar')) {
            return true;
        }

        return DB::table('centros')
            ->where('centros.empresa_id', $usuario->empresa_id)
            ->whereExists(function ($consulta) use ($usuario) {
                $consulta->selectRaw('1')
                    ->from('v_usuarios_permisos as vp')
                    ->where('vp.usuario_id', $usuario->id)
                    ->where('vp.permiso', 'grupos.editar')
                    ->where(function ($ambito) {
                        $ambito->whereNull('vp.centro_id')
                            ->orWhereColumn('vp.centro_id', 'centros.id');
                    });
            })
            ->exists();
    }


    //.................................. puedeCrear ....................................................
    public function puedeCrear(): bool
    {
        $usuario = $this->usuarioActual();

        return $this->esAdministradorGeneral(
            $usuario,
            'grupos.editar'
        ) || $this->tieneAlgunCentroEditable($usuario);
    }


    //.................................. autorizarCreacion ....................................................
    public function autorizarCreacion(): void
    {
        if (!$this->puedeCrear()) {
            throw new AuthorizationException(
                'No tienes permiso para crear grupos.'
            );
        }
    }


    //.................................. consultaGrupos ....................................................
    private function consultaGrupos(string $permiso)
    {
        $usuario = $this->usuarioActual();

        $consulta = DB::table('grupos')
            ->join('temporadas', 'temporadas.id', '=', 'grupos.temporada_id')
            ->join('cursos', 'cursos.id', '=', 'grupos.curso_id')
            ->join('centros', 'centros.id', '=', 'grupos.centro_id')
            ->leftJoin('plataformas', 'plataformas.id', '=', 'grupos.plataforma_id')
            ->whereColumn('centros.empresa_id', 'temporadas.empresa_id')
            ->select(
                'grupos.*',
                'grupos.centro_id',
                'temporadas.nombre as temporada_nombre',
                'temporadas.empresa_id as temporada_empresa_id',
                'cursos.nombre as curso_nombre',
                'centros.nombre as centro_nombre',
                'centros.empresa_id as centro_empresa_id',
                'plataformas.nombre as plataforma_nombre'
            );

        if ($this->esAdministradorGeneral($usuario, $permiso)) {
            return $consulta;
        }

        return $consulta
            ->where('centros.empresa_id', $usuario->empresa_id)
            ->whereExists(function ($subconsulta) use ($usuario, $permiso) {
                $subconsulta->selectRaw('1')
                    ->from('v_usuarios_permisos as vp')
                    ->where('vp.usuario_id', $usuario->id)
                    ->where('vp.permiso', $permiso)
                    ->where(function ($ambito) {
                        $ambito->whereNull('vp.centro_id')
                            ->orWhereColumn(
                                'vp.centro_id',
                                'grupos.centro_id'
                            );
                    });
            });
    }


    //.................................. listar ....................................................
    public function listar()
    {
        return $this->consultaGrupos('grupos.ver')
            ->orderBy('grupos.nombre')
            ->get();
    }


    //.................................. listarPorCentro ....................................................
    public function listarPorCentro(int $centroId)
    {
        // Comprobar que el usuario puede consultar grupos de este centro.
        $this->autorizarCentro($centroId, 'grupos.ver');

        return $this->consultaGrupos('grupos.ver')
            ->where('grupos.centro_id', $centroId)
            ->orderBy('grupos.nombre')
            ->get();
    }


    //.................................. obtener ....................................................
    public function obtener(int $grupoId)
    {
        return $this->consultaGrupos('grupos.ver')
            ->where('grupos.id', $grupoId)
            ->firstOrFail();
    }


    //.................................. obtenerParaEditar ....................................................
    public function obtenerParaEditar(int $grupoId)
    {
        return $this->consultaGrupos('grupos.editar')
            ->where('grupos.id', $grupoId)
            ->firstOrFail();
    }


    //.................................. puedeEditar ....................................................
    public function puedeEditar(int $grupoId): bool
    {
        return $this->consultaGrupos('grupos.editar')
            ->where('grupos.id', $grupoId)
            ->exists();
    }


    //.................................. temporadasDisponibles ....................................................
    public function temporadasDisponibles(
        ?int $temporadaActualId = null
    ) {
        $usuario = $this->usuarioActual();

        $consulta = DB::table('temporadas');

        if (!$this->esAdministradorGeneral($usuario, 'grupos.editar')) {
            $consulta->where('empresa_id', $usuario->empresa_id);
        }

        $consulta->where(function ($filtro) use ($temporadaActualId) {
            $filtro->where('estado', '<>', 'cerrada');

            if ($temporadaActualId !== null) {
                $filtro->orWhere('id', $temporadaActualId);
            }
        });

        return $consulta
            ->orderByDesc('fecha_inicio')
            ->get();
    }


    //.................................. obtenerTemporadaAutorizada ....................................................
    private function obtenerTemporadaAutorizada(int $temporadaId)
    {
        $usuario = $this->usuarioActual();

        $consulta = DB::table('temporadas')
            ->where('id', $temporadaId);

        if (!$this->esAdministradorGeneral($usuario, 'grupos.editar')) {
            $consulta->where('empresa_id', $usuario->empresa_id);
        }

        return $consulta->firstOrFail();
    }


    //.................................. autorizarCentro ....................................................
    private function autorizarCentro(
        int $centroId,
        string $permiso
    ) {
        $usuario = $this->usuarioActual();

        $esAdministradorGeneral = $this->esAdministradorGeneral(
            $usuario,
            $permiso
        );

        $consulta = DB::table('centros')
            ->where('id', $centroId);

        if (!$esAdministradorGeneral) {
            $consulta->where('empresa_id', $usuario->empresa_id);
        }

        $centro = $consulta->first();

        if (!$centro) {
            throw new AuthorizationException(
                'No tienes acceso a este centro.'
            );
        }

        if (!$this->tienePermisoCentro(
            $usuario,
            $permiso,
            $centroId
        )) {
            throw new AuthorizationException(
                'No tienes permiso para acceder a los grupos de este centro.'
            );
        }

        return $centro;
    }


    //.................................. opciones ....................................................
    public function opciones(int $temporadaId): array
    {
        $temporada = $this->obtenerTemporadaAutorizada($temporadaId);

        $usuario = $this->usuarioActual();

        $centros = DB::table('centros')
            ->where('empresa_id', $temporada->empresa_id)
            ->where('activo', true)
            ->whereExists(function ($consulta) use ($usuario) {
                $consulta->selectRaw('1')
                    ->from('v_usuarios_permisos as vp')
                    ->where('vp.usuario_id', $usuario->id)
                    ->where('vp.permiso', 'grupos.editar')
                    ->where(function ($ambito) {
                        $ambito->whereNull('vp.centro_id')
                            ->orWhereColumn('vp.centro_id', 'centros.id');
                    });
            })
            ->orderBy('nombre')
            ->get();

        if ($this->esAdministradorGeneral($usuario, 'grupos.editar')) {
            $centros = DB::table('centros')
                ->where('empresa_id', $temporada->empresa_id)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        }

        $cursos = DB::table('cursos')
            ->where('empresa_id', $temporada->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $plataformas = DB::table('plataformas')
            ->where('empresa_id', $temporada->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return [
            'temporada' => $temporada,
            'cursos' => $cursos,
            'centros' => $centros,
            'plataformas' => $plataformas,
        ];
    }


    //.................................. validarDatos ....................................................
    private function validarDatos(
        array $datos,
        ?int $grupoId = null,
        bool $permitirTemporadaCerrada = false,
        ?int $temporadaActualId = null
    ): array {
        $validador = Validator::make($datos, [
            'temporada_id' => [
                'required',
                'integer',
                'exists:temporadas,id',
            ],
            'curso_id' => [
                'required',
                'integer',
                'exists:cursos,id',
            ],
            'centro_id' => [
                'required',
                'integer',
                'exists:centros,id',
            ],
            'plataforma_id' => [
                'nullable',
                'integer',
                'exists:plataformas,id',
            ],
            'nombre' => [
                'required',
                'string',
                'max:80',
            ],
            'capacidad_maxima' => [
                'required',
                'integer',
                'between:1,255',
            ],
            'estado' => [
                'required',
                'in:planificado,abierto,en_curso,finalizado,cancelado',
            ],
        ]);

        $datosValidados = $validador->validate();

        $temporada = $this->obtenerTemporadaAutorizada(
            (int) $datosValidados['temporada_id']
        );

        if (
            $temporada->estado === 'cerrada'
            && !(
                $permitirTemporadaCerrada
                && $temporadaActualId === (int) $temporada->id
            )
        ) {
            throw ValidationException::withMessages([
                'temporada_id' => 'No se puede asignar el grupo a una temporada cerrada.',
            ]);
        }

        $centro = $this->autorizarCentro(
            (int) $datosValidados['centro_id'],
            'grupos.editar'
        );

        if ((int) $centro->empresa_id !== (int) $temporada->empresa_id) {
            throw ValidationException::withMessages([
                'centro_id' => 'El centro no pertenece a la empresa de la temporada.',
            ]);
        }

        $curso = DB::table('cursos')
            ->where('id', $datosValidados['curso_id'])
            ->where('empresa_id', $temporada->empresa_id)
            ->where('activo', true)
            ->first();

        if (!$curso) {
            throw ValidationException::withMessages([
                'curso_id' => 'El curso no pertenece a la empresa de la temporada o no está activo.',
            ]);
        }

        if (!empty($datosValidados['plataforma_id'])) {
            $plataforma = DB::table('plataformas')
                ->where('id', $datosValidados['plataforma_id'])
                ->where('empresa_id', $temporada->empresa_id)
                ->where('activo', true)
                ->first();

            if (!$plataforma) {
                throw ValidationException::withMessages([
                    'plataforma_id' => 'La plataforma no pertenece a la empresa de la temporada o no está activa.',
                ]);
            }
        }

        $duplicado = DB::table('grupos')
            ->where('curso_id', $datosValidados['curso_id'])
            ->where('centro_id', $datosValidados['centro_id'])
            ->where('nombre', $datosValidados['nombre']);

        if ($grupoId !== null) {
            $duplicado->where('id', '<>', $grupoId);
        }

        if ($duplicado->exists()) {
            throw ValidationException::withMessages([
                'nombre' => 'Ya existe un grupo con ese nombre para este curso y centro.',
            ]);
        }

        // Las fechas del grupo siempre coinciden con las de su temporada.
        $datosValidados['fecha_inicio'] = $temporada->fecha_inicio;
        $datosValidados['fecha_fin'] = $temporada->fecha_fin;

        return $datosValidados;
    }


    //.................................. crear ....................................................
    public function crear(
        array $datos,
        ?int $usuarioRolId = null
    ): int {
        $this->autorizarCreacion();

        return DB::transaction(function () use ($datos, $usuarioRolId) {
            $datos = $this->validarDatos($datos);

            $ahora = now();

            return DB::table('grupos')->insertGetId([
                'temporada_id' => $datos['temporada_id'],
                'curso_id' => $datos['curso_id'],
                'centro_id' => $datos['centro_id'],
                'plataforma_id' => $datos['plataforma_id'] ?? null,
                'nombre' => $datos['nombre'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'capacidad_maxima' => $datos['capacidad_maxima'],
                'estado' => $datos['estado'],
                'persona_id_created' => $usuarioRolId,
                'persona_id_updated' => $usuarioRolId,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        });
    }


    //.................................. actualizar ....................................................
    public function actualizar(
        int $grupoId,
        array $datos,
        ?int $usuarioRolId = null
    ): void {
        DB::transaction(function () use ($grupoId, $datos, $usuarioRolId) {
            $grupo = DB::table('grupos')
                ->where('id', $grupoId)
                ->lockForUpdate()
                ->first();

            if (!$grupo) {
                abort(404);
            }

            $this->autorizarCentro(
                (int) $grupo->centro_id,
                'grupos.editar'
            );

            $datos = $this->validarDatos(
                $datos,
                $grupoId,
                true,
                (int) $grupo->temporada_id
            );

            DB::table('grupos')
                ->where('id', $grupoId)
                ->update([
                    'temporada_id' => $datos['temporada_id'],
                    'curso_id' => $datos['curso_id'],
                    'centro_id' => $datos['centro_id'],
                    'plataforma_id' => $datos['plataforma_id'] ?? null,
                    'nombre' => $datos['nombre'],
                    'fecha_inicio' => $datos['fecha_inicio'],
                    'fecha_fin' => $datos['fecha_fin'],
                    'capacidad_maxima' => $datos['capacidad_maxima'],
                    'estado' => $datos['estado'],
                    'persona_id_updated' => $usuarioRolId,
                    'updated_at' => now(),
                ]);
        });
    }
}
