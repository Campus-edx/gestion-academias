<?php

namespace App\Services;

use App\Models\Empresas;
use App\Models\Centros;
use App\Models\Usuarios;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CentrosService
{

    //.................................. autorizarCreacion ...................................
    public function autorizarCreacion(): void
    {
        $usuario = $this->usuarioActual();

        $esAdministradorGeneral = $this->esAdministradorGeneral(
            $usuario,
            'centros.editar'
        );

        if (
            !$esAdministradorGeneral
            && !$this->tienePermisoGlobalEmpresa(
                $usuario,
                'centros.editar'
            )
        ) {
            throw new AuthorizationException(
                'No tienes permiso para crear centros.'
            );
        }
    }

    //.................................. puedeCrear ...................................
    public function puedeCrear(): bool
    {
        $usuario = $this->usuarioActual();

        return $this->esAdministradorGeneral(
            $usuario,
            'centros.editar'
        ) || $this->tienePermisoGlobalEmpresa(
            $usuario,
            'centros.editar'
        );
    }

    //.................................. empresasDisponibles ...................................
    public function empresasDisponibles(): Collection
    {
        $usuario = $this->usuarioActual();

        if ($this->esAdministradorGeneral($usuario, 'centros.editar')) {
            return Empresas::query()
                ->orderBy('razon_social')
                ->get();
        }

        return Empresas::query()
            ->where('id', $usuario->empresa_id)
            ->orderBy('razon_social')
            ->get();
    }

    //.................................. obtenerParaEditar ....................................................
    public function obtenerParaEditar(int $centroId): Centros
    {
        $usuario = $this->usuarioActual();

        return $this->consultaCentrosAccesibles(
            $usuario,
            'centros.editar'
        )
            ->with($this->relacionesFicha())
            ->findOrFail($centroId);
    }


    //.................................. listar ....................................................
    public function listar(): Collection
    {
        $usuario = $this->usuarioActual();

        return $this->consultaCentrosAccesibles($usuario, 'centros.ver')
            ->with([
                'empresa',
                'direcciones.direccion',
            ])
            ->orderBy('nombre')
            ->get();
    }

    //.................................. obtener ....................................................
    public function obtener(int $centroId): Centros
    {
        $usuario = $this->usuarioActual();

        return $this->consultaCentrosAccesibles($usuario, 'centros.ver')
            ->with($this->relacionesFicha())
            ->findOrFail($centroId);
    }

    //.................................. crear ....................................................

    public function crear(
        array $datos,
        ?int $usuarioRolId = null
    ): Centros {
        $usuario = $this->usuarioActual();

        $esAdministradorGeneral = $this->esAdministradorGeneral(
            $usuario,
            'centros.editar'
        );

        // Un usuario normal solo puede crear centros en su empresa.
        if (
            !$esAdministradorGeneral
            && (int) $datos['empresa_id'] !== (int) $usuario->empresa_id
        ) {
            throw new AuthorizationException(
                'No puedes crear centros para otra empresa.'
            );
        }

        // Para crear se necesita un permiso global de edición.
        if (
            !$esAdministradorGeneral
            && !$this->tienePermisoGlobalEmpresa(
                $usuario,
                'centros.editar'
            )
        ) {
            throw new AuthorizationException(
                'No tienes permiso para crear centros.'
            );
        }

        return DB::transaction(function () use (
            $datos,
            $usuarioRolId
        ) {
            $datosCentro = $this->filtrarDatos($datos);

            $datosCentro['activo'] = $datosCentro['activo'] ?? true;
            $datosCentro['persona_id_created'] = $usuarioRolId;

            $centro = Centros::create($datosCentro);

            return $centro->load($this->relacionesFicha());
        });
    }

    //.................................. actualizar ....................................................
    public function actualizar(
        int $centroId,
        array $datos,
        ?int $usuarioRolId = null
    ): Centros {
        $usuario = $this->usuarioActual();

        $esAdministradorGeneral = $this->esAdministradorGeneral(
            $usuario,
            'centros.editar'
        );

        return DB::transaction(function () use (
            $centroId,
            $datos,
            $usuarioRolId,
            $usuario,
            $esAdministradorGeneral
        ) {
            $centro = $this->consultaCentrosAccesibles(
                $usuario,
                'centros.editar'
            )
                ->lockForUpdate()
                ->findOrFail($centroId);

            // Impedir que un usuario normal cambie el centro
            // a una empresa distinta de la suya.
            if (
                !$esAdministradorGeneral
                && (int) $datos['empresa_id'] !== (int) $usuario->empresa_id
            ) {
                throw new AuthorizationException(
                    'No puedes asignar el centro a otra empresa.'
                );
            }

            $datosCentro = $this->filtrarDatos($datos);

            $datosCentro['persona_id_updated'] = $usuarioRolId;

            $centro->update($datosCentro);

            return $centro->refresh()->load($this->relacionesFicha());
        });
    }


    //.................................. cambiarEstado ....................................................
    public function cambiarEstado(
        int $centroId,
        bool $activo,
        ?int $usuarioRolId = null
    ): Centros {
        $usuario = $this->usuarioActual();

        return DB::transaction(function () use (
            $centroId,
            $activo,
            $usuarioRolId,
            $usuario
        ) {
            $centro = $this->consultaCentrosAccesibles(
                $usuario,
                'centros.editar'
            )
                ->lockForUpdate()
                ->findOrFail($centroId);

            $centro->update([
                'activo' => $activo,
                'persona_id_updated' => $usuarioRolId,
            ]);

            return $centro->refresh()->load($this->relacionesFicha());
        });
    }


    //.................................. consultaCentrosAccesibles ....................................................
    private function consultaCentrosAccesibles(
        Usuarios $usuario,
        string $permiso
    ): Builder {
        // El administrador general con el permiso vigente
        // puede consultar centros de cualquier empresa.
        if ($this->esAdministradorGeneral($usuario, $permiso)) {
            return Centros::query();
        }

        // Los demás usuarios quedan limitados a su empresa.
        return Centros::query()
            ->where('centros.empresa_id', $usuario->empresa_id)
            ->whereExists(function ($consulta) use ($usuario, $permiso) {
                $consulta
                    ->selectRaw('1')
                    ->from('usuario_rol as ur')
                    ->join('roles as r', 'r.id', '=', 'ur.rol_id')
                    ->join('rol_permiso as rp', 'rp.rol_id', '=', 'r.id')
                    ->join('permisos as p', 'p.id', '=', 'rp.permiso_id')
                    ->where('ur.usuario_id', $usuario->id)
                    ->where('p.codigo', $permiso)
                    ->where('ur.fecha_inicio', '<=', now()->toDateString())
                    ->where(function ($vigencia) {
                        $vigencia
                            ->whereNull('ur.fecha_fin')
                            ->orWhere('ur.fecha_fin', '>=', now()->toDateString());
                    })
                    ->where(function ($ambito) {
                        $ambito
                            ->whereNull('ur.centro_id')
                            ->orWhereColumn('ur.centro_id', 'centros.id');
                    });
            });
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
                $vigencia
                    ->whereNull('ur.fecha_fin')
                    ->orWhere('ur.fecha_fin', '>=', now()->toDateString());
            })
            ->exists();
    }


    //.................................. tienePermisoGlobalEmpresa ....................................................
    private function tienePermisoGlobalEmpresa(
        Usuarios $usuario,
        string $permiso
    ): bool {
        return DB::table('usuario_rol as ur')
            ->join('roles as r', 'r.id', '=', 'ur.rol_id')
            ->join('rol_permiso as rp', 'rp.rol_id', '=', 'r.id')
            ->join('permisos as p', 'p.id', '=', 'rp.permiso_id')
            ->where('ur.usuario_id', $usuario->id)
            ->where('p.codigo', $permiso)
            ->whereNull('ur.centro_id')
            ->where('ur.fecha_inicio', '<=', now()->toDateString())
            ->where(function ($vigencia) {
                $vigencia
                    ->whereNull('ur.fecha_fin')
                    ->orWhere('ur.fecha_fin', '>=', now()->toDateString());
            })
            ->exists();
    }

    //.................................. usuarioActual ....................................................
    private function usuarioActual(): Usuarios
    {
        $usuario = Auth::user();

        if (!$usuario instanceof Usuarios) {
            throw new AuthorizationException(
                'Debes iniciar sesión para acceder a los centros.'
            );
        }

        return $usuario;
    }


    //.................................. relacionesFicha ....................................................
    private function relacionesFicha(): array
    {
        return [
            'empresa',
            'direcciones.direccion.municipio.provincia.comunidadAutonoma.pais',
            'telefonos.telefono',
            'correos.correoElectronico',
            'grupos',
        ];
    }


    //.................................. filtrarDatos ....................................................
    private function filtrarDatos(array $datos): array
    {
        return array_intersect_key(
            $datos,
            array_flip([
                'empresa_id',
                'nombre',
                'activo',
            ])
        );
    }
}
