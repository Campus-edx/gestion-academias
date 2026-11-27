<?php

namespace App\Services;

use App\Models\Centros;
use App\Models\Contactos\Direcciones;
use App\Models\Contactos\EntidadesDirecciones;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DireccionesService
{
    /**
     * Obtener las direcciones de un centro.
     */
    public function deCentro(int $centroId): Collection
    {
        return EntidadesDirecciones::query()
            ->with('direccion')
            ->where('centro_id', $centroId)
            ->orderByDesc('principal')
            ->orderBy('id')
            ->get();
    }

    /**
     * Crear una dirección y asociarla a un centro.
     */
    public function crearParaCentro(
        int $centroId,
        array $datos,
        ?int $usuarioRolId = null
    ): EntidadesDirecciones {
        return DB::transaction(function () use (
            $centroId,
            $datos,
            $usuarioRolId
        ) {
            Centros::query()
                ->lockForUpdate()
                ->findOrFail($centroId);

            $activo = (bool) ($datos['activo'] ?? true);
            $principal = (bool) ($datos['principal'] ?? false);

            $this->validarPrincipal($principal, $activo);
            $this->validarTipo($datos['tipo'] ?? 'general');

            if ($principal) {
                $this->desmarcarPrincipales(
                    $centroId,
                    null,
                    $usuarioRolId
                );
            }

            $direccion = Direcciones::create([
                'linea_direccion_1' => trim($datos['linea_direccion_1']),
                'linea_direccion_2' => $this->limpiar(
                    $datos['linea_direccion_2'] ?? null
                ),
                'codigo_postal' => $this->limpiar(
                    $datos['codigo_postal'] ?? null
                ),
                'municipio_id' => (int) $datos['municipio_id'],
                'observaciones' => $this->limpiar(
                    $datos['observaciones'] ?? null
                ),
                'requiere_revision' => (bool) (
                    $datos['requiere_revision'] ?? false
                ),
                'persona_id_created' => $usuarioRolId,
            ]);

            $relacion = EntidadesDirecciones::create([
                'centro_id' => $centroId,
                'direccion_id' => $direccion->id,
                'tipo' => $datos['tipo'] ?? 'general',
                'principal' => $principal,
                'activo' => $activo,
                'observaciones' => $this->limpiar(
                    $datos['observaciones_relacion'] ?? null
                ),
                'persona_id_created' => $usuarioRolId,
            ]);

            return $relacion->load('direccion');
        });
    }

    /**
     * Actualizar una dirección perteneciente a un centro.
     *
     * Si está compartida con otras entidades, crea una copia
     * para evitar modificar los datos de las demás entidades.
     */
    public function actualizarDeCentro(
        int $centroId,
        int $relacionId,
        array $datos,
        ?int $usuarioRolId = null
    ): EntidadesDirecciones {
        return DB::transaction(function () use (
            $centroId,
            $relacionId,
            $datos,
            $usuarioRolId
        ) {
            Centros::query()
                ->lockForUpdate()
                ->findOrFail($centroId);

            $relacion = EntidadesDirecciones::query()
                ->where('centro_id', $centroId)
                ->whereKey($relacionId)
                ->lockForUpdate()
                ->firstOrFail();

            $direccion = Direcciones::query()
                ->lockForUpdate()
                ->findOrFail($relacion->direccion_id);

            $activo = (bool) (
                $datos['activo'] ?? $relacion->activo
            );

            $principal = (bool) (
                $datos['principal'] ?? $relacion->principal
            );

            $principal = $activo && $principal;

            $tipo = $datos['tipo'] ?? $relacion->tipo;

            $this->validarTipo($tipo);

            if ($principal) {
                $this->desmarcarPrincipales(
                    $centroId,
                    (int) $relacion->id,
                    $usuarioRolId
                );
            }

            // Comprobar si otras entidades utilizan esta dirección.
            $compartida = EntidadesDirecciones::query()
                ->where('direccion_id', $direccion->id)
                ->where('id', '!=', $relacion->id)
                ->exists();

            $datosDireccion = [
                'linea_direccion_1' => trim(
                    $datos['linea_direccion_1']
                ),
                'linea_direccion_2' => $this->limpiar(
                    $datos['linea_direccion_2'] ?? null
                ),
                'codigo_postal' => $this->limpiar(
                    $datos['codigo_postal'] ?? null
                ),
                'municipio_id' => (int) $datos['municipio_id'],
                'observaciones' => $this->limpiar(
                    $datos['observaciones'] ?? null
                ),
                'requiere_revision' => (bool) (
                    $datos['requiere_revision'] ?? false
                ),
            ];

            if ($compartida) {
                // Crear una dirección independiente para este centro.
                $nuevaDireccion = Direcciones::create([
                    ...$datosDireccion,
                    'persona_id_created' => $usuarioRolId,
                ]);

                $relacion->direccion_id = $nuevaDireccion->id;
            } else {
                // Actualizar la dirección si no está compartida.
                $direccion->update([
                    ...$datosDireccion,
                    'persona_id_updated' => $usuarioRolId,
                ]);
            }

            $relacion->update([
                'direccion_id' => $relacion->direccion_id,
                'tipo' => $tipo,
                'principal' => $principal,
                'activo' => $activo,
                'observaciones' => $this->limpiar(
                    $datos['observaciones_relacion']
                        ?? $relacion->observaciones
                ),
                'persona_id_updated' => $usuarioRolId,
            ]);

            return $relacion->refresh()->load('direccion');
        });
    }

    /**
     * Marcar una dirección como principal.
     */
    public function marcarPrincipal(
        int $centroId,
        int $relacionId,
        ?int $usuarioRolId = null
    ): EntidadesDirecciones {
        return DB::transaction(function () use (
            $centroId,
            $relacionId,
            $usuarioRolId
        ) {
            Centros::query()
                ->lockForUpdate()
                ->findOrFail($centroId);

            $relacion = EntidadesDirecciones::query()
                ->where('centro_id', $centroId)
                ->whereKey($relacionId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $relacion->activo) {
                throw ValidationException::withMessages([
                    'principal' => 'No se puede marcar como principal una dirección inactiva.',
                ]);
            }

            $this->desmarcarPrincipales(
                $centroId,
                (int) $relacion->id,
                $usuarioRolId
            );

            $relacion->update([
                'principal' => true,
                'persona_id_updated' => $usuarioRolId,
            ]);

            return $relacion->refresh()->load('direccion');
        });
    }

    /**
     * Desvincular una dirección de un centro.
     *
     * No elimina el registro físico de la tabla direcciones.
     */
    public function desvincularDeCentro(
        int $centroId,
        int $relacionId
    ): void {
        DB::transaction(function () use ($centroId, $relacionId) {
            Centros::query()
                ->lockForUpdate()
                ->findOrFail($centroId);

            $relacion = EntidadesDirecciones::query()
                ->where('centro_id', $centroId)
                ->whereKey($relacionId)
                ->lockForUpdate()
                ->firstOrFail();

            $relacion->delete();
        });
    }

    /**
     * Desmarcar las demás direcciones principales del centro.
     */
    private function desmarcarPrincipales(
        int $centroId,
        ?int $exceptId = null,
        ?int $usuarioRolId = null
    ): void {
        EntidadesDirecciones::query()
            ->where('centro_id', $centroId)
            ->where('principal', true)
            ->when(
                $exceptId !== null,
                fn($query) => $query->where('id', '!=', $exceptId)
            )
            ->update([
                'principal' => false,
                'persona_id_updated' => $usuarioRolId,
            ]);
    }

    /**
     * Validar que una dirección principal esté activa.
     */
    private function validarPrincipal(
        bool $principal,
        bool $activo
    ): void {
        if ($principal && ! $activo) {
            throw ValidationException::withMessages([
                'principal' => 'La dirección principal debe estar activa.',
            ]);
        }
    }

    /**
     * Validar el tipo de dirección.
     */
    private function validarTipo(string $tipo): void
    {
        if (! in_array($tipo, ['general', 'fiscal', 'postal'], true)) {
            throw ValidationException::withMessages([
                'tipo' => 'El tipo de dirección seleccionado no es válido.',
            ]);
        }
    }

    /**
     * Limpiar valores de texto opcionales.
     */
    private function limpiar(mixed $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }
}
