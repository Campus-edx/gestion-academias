<?php

namespace App\Services;

use App\Models\Empresas;
use App\Models\Temporadas\Temporadas;
use App\Models\Usuarios;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TemporadasService
{
    /**
     * Obtener el usuario autenticado.
     */
    private function usuarioActual(): Usuarios
    {
        $usuario = Auth::user();

        if (!$usuario instanceof Usuarios) {
            throw new AuthorizationException(
                'Debes iniciar sesión para acceder a las temporadas.'
            );
        }

        return $usuario;
    }

    /**
     * Comprobar si el usuario tiene permiso global.
     */
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

    /**
     * Comprobar si es administrador general y tiene el permiso.
     */
    private function esAdministradorGeneral(
        Usuarios $usuario,
        string $permiso
    ): bool {
        return DB::table('usuario_rol as ur')
            ->join('roles as r', 'r.id', '=', 'ur.rol_id')
            ->join('rol_permiso as rp', 'rp.rol_id', '=', 'r.id')
            ->join('permisos as p', 'p.id', '=', 'rp.permiso_id')
            ->where('ur.usuario_id', $usuario->id)
            ->whereNull('ur.centro_id')
            ->where('r.codigo', 'administrador_general')
            ->where('p.codigo', $permiso)
            ->where('ur.fecha_inicio', '<=', now()->toDateString())
            ->where(function ($vigencia) {
                $vigencia->whereNull('ur.fecha_fin')
                    ->orWhere('ur.fecha_fin', '>=', now()->toDateString());
            })
            ->exists();
    }

    /**
     * Comprobar si tiene permiso para gestionar temporadas
     * de una empresa concreta.
     *
     * Una asignación a un centro habilita el acceso a las
     * temporadas de la empresa a la que pertenece ese centro.
     */
    private function tienePermisoEnEmpresa(
        Usuarios $usuario,
        string $permiso,
        int $empresaId
    ): bool {
        return DB::table('v_usuarios_permisos as vp')
            ->where('vp.usuario_id', $usuario->id)
            ->where('vp.permiso', $permiso)
            ->where(function ($ambito) use ($empresaId) {
                $ambito->whereNull('vp.centro_id')
                    ->orWhereExists(function ($centros) use ($empresaId) {
                        $centros->selectRaw('1')
                            ->from('centros')
                            ->whereColumn('centros.id', 'vp.centro_id')
                            ->where('centros.empresa_id', $empresaId);
                    });
            })
            ->exists();
    }

    /**
     * Comprobar si puede crear temporadas.
     */
    public function puedeCrear(): bool
    {
        $usuario = $this->usuarioActual();

        if ($this->esAdministradorGeneral($usuario, 'empresas.editar')) {
            return true;
        }

        if ($usuario->empresa_id === null) {
            return false;
        }

        return $this->tienePermisoEnEmpresa(
            $usuario,
            'empresas.editar',
            (int) $usuario->empresa_id
        );
    }

    /**
     * Autorizar la creación de temporadas.
     */
    public function autorizarCreacion(): void
    {
        if (!$this->puedeCrear()) {
            throw new AuthorizationException(
                'No tienes permiso para crear temporadas.'
            );
        }
    }

    /**
     * Autorizar la consulta de temporadas.
     */
    public function autorizarConsulta(): void
    {
        $usuario = $this->usuarioActual();

        if ($this->esAdministradorGeneral($usuario, 'empresas.ver')) {
            return;
        }

        if (
            $usuario->empresa_id === null
            || !$this->tienePermisoEnEmpresa(
                $usuario,
                'empresas.ver',
                (int) $usuario->empresa_id
            )
        ) {
            throw new AuthorizationException(
                'No tienes permiso para consultar temporadas.'
            );
        }
    }

    /**
     * Construir una consulta limitada a las temporadas autorizadas.
     */
    private function consultaTemporadas(string $permiso)
    {
        $usuario = $this->usuarioActual();

        $consulta = Temporadas::query()
            ->with('empresa');

        if ($this->esAdministradorGeneral($usuario, $permiso)) {
            return $consulta;
        }

        if ($usuario->empresa_id === null) {
            return $consulta->whereRaw('1 = 0');
        }

        return $consulta
            ->where('temporadas.empresa_id', $usuario->empresa_id)
            ->whereExists(function ($subconsulta) use ($usuario, $permiso) {
                $subconsulta->selectRaw('1')
                    ->from('v_usuarios_permisos as vp')
                    ->where('vp.usuario_id', $usuario->id)
                    ->where('vp.permiso', $permiso)
                    ->where(function ($ambito) {
                        $ambito->whereNull('vp.centro_id')
                            ->orWhereExists(function ($centros) {
                                $centros->selectRaw('1')
                                    ->from('centros')
                                    ->whereColumn('centros.id', 'vp.centro_id')
                                    ->whereColumn(
                                        'centros.empresa_id',
                                        'temporadas.empresa_id'
                                    );
                            });
                    });
            });
    }

    /**
     * Listar las temporadas autorizadas.
     */
    public function listar(?string $buscar = null)
    {
        $this->autorizarConsulta();

        return $this->consultaTemporadas('empresas.ver')
            ->when($buscar, function ($consulta, $buscar) {
                $consulta->where(function ($subconsulta) use ($buscar) {
                    $subconsulta->where('temporadas.nombre', 'like', "%{$buscar}%")
                        ->orWhereHas('empresa', function ($empresa) use ($buscar) {
                            $empresa->where('razon_social', 'like', "%{$buscar}%")
                                ->orWhere('nombre_comercial', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderBy('temporadas.fecha_inicio', 'asc')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Obtener una temporada dentro del ámbito de consulta autorizado.
     */
    public function obtener(int $temporadaId): Temporadas
    {
        return $this->consultaTemporadas('empresas.ver')
            ->where('temporadas.id', $temporadaId)
            ->firstOrFail();
    }

    /**
     * Obtener una temporada dentro del ámbito de edición autorizado.
     */
    public function obtenerParaEditar(int $temporadaId): Temporadas
    {
        return $this->consultaTemporadas('empresas.editar')
            ->where('temporadas.id', $temporadaId)
            ->firstOrFail();
    }

    /**
     * Comprobar si puede editar una temporada concreta.
     */
    public function puedeEditar(int $temporadaId): bool
    {
        return $this->consultaTemporadas('empresas.editar')
            ->where('temporadas.id', $temporadaId)
            ->exists();
    }

    /**
     * Obtener las empresas disponibles para crear o editar temporadas.
     */
    public function empresasDisponibles(
        ?int $empresaActualId = null
    ) {
        $usuario = $this->usuarioActual();

        $consulta = Empresas::query();

        if (!$this->esAdministradorGeneral($usuario, 'empresas.editar')) {
            if (
                $usuario->empresa_id === null
                || !$this->tienePermisoEnEmpresa(
                    $usuario,
                    'empresas.editar',
                    (int) $usuario->empresa_id
                )
            ) {
                return $consulta->whereRaw('1 = 0')->get();
            }

            $consulta->where('id', $usuario->empresa_id);
        }

        $consulta->where(function ($filtro) use ($empresaActualId) {
            $filtro->where('activo', true);

            if ($empresaActualId !== null) {
                $filtro->orWhere('id', $empresaActualId);
            }
        });

        return $consulta
            ->orderBy('nombre_comercial')
            ->get();
    }

    /**
     * Validar los datos de una temporada.
     */
    private function validarDatos(
        array $datos,
        ?Temporadas $temporadaActual = null
    ): array {
        $empresas = $this->empresasDisponibles(
            $temporadaActual?->empresa_id
        );

        $reglasEmpresa = [
            'required',
            'integer',
            Rule::in($empresas->pluck('id')->all()),
        ];

        if ($temporadaActual === null) {
            $reglasEmpresa[] = Rule::exists('empresas', 'id')
                ->where('activo', true);
        }

        $reglas = [
            'empresa_id' => $reglasEmpresa,
            'nombre' => [
                'required',
                'string',
                'max:80',
                Rule::unique('temporadas', 'nombre')
                    ->where('empresa_id', $datos['empresa_id'] ?? null)
                    ->ignore($temporadaActual?->id),
            ],
            'fecha_inicio' => [
                'required',
                'date',
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],
            'estado' => [
                'required',
                Rule::in(['planificada', 'activa', 'cerrada']),
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];

        return Validator::make($datos, $reglas)->validate();
    }

    /**
     * Crear una temporada.
     */
    public function crear(
        array $datos,
        ?int $usuarioRolId = null
    ): int {
        $this->autorizarCreacion();

        return DB::transaction(function () use ($datos, $usuarioRolId) {
            $datos = $this->validarDatos($datos);

            $datos['persona_id_created'] = $usuarioRolId;
            $datos['persona_id_updated'] = $usuarioRolId;

            return Temporadas::create($datos)->id;
        });
    }

    /**
     * Actualizar una temporada.
     */
    public function actualizar(
        int $temporadaId,
        array $datos,
        ?int $usuarioRolId = null
    ): void {
        DB::transaction(function () use (
            $temporadaId,
            $datos,
            $usuarioRolId
        ) {
            $temporada = $this->obtenerParaEditar($temporadaId);

            $datos = $this->validarDatos($datos, $temporada);

            $datos['persona_id_updated'] = $usuarioRolId;

            $temporada->update($datos);
        });
    }

    /**
     * Eliminar una temporada si no tiene grupos asociados.
     */
    public function eliminar(int $temporadaId): void
    {
        DB::transaction(function () use ($temporadaId) {
            $temporada = $this->obtenerParaEditar($temporadaId);

            if ($temporada->grupos()->exists()) {
                throw ValidationException::withMessages([
                    'temporada' => 'No se puede eliminar la temporada porque tiene grupos asociados.',
                ]);
            }

            $temporada->delete();
        });
    }
}
