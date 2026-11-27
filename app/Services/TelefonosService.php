<?php

namespace App\Services;

use App\Models\Centros;
use App\Models\Contactos\Telefonos;
use App\Models\Contactos\EntidadesTelefonos;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TelefonosService
{
    public function deCentro(int $centroId): Collection
    {
        return EntidadesTelefonos::query()
            ->with('telefono')
            ->where('centro_id', $centroId)
            ->orderByDesc('principal')
            ->orderBy('id')
            ->get();
    }

    public function crearParaCentro(
        int $centroId,
        array $datos,
        ?int $usuarioRolId = null
    ): EntidadesTelefonos {
        return DB::transaction(function () use ($centroId, $datos, $usuarioRolId) {
            Centros::query()->lockForUpdate()->findOrFail($centroId);

            $activo = (bool) ($datos['activo'] ?? true);
            $principal = (bool) ($datos['principal'] ?? false);

            $this->validarPrincipal($principal, $activo);

            if ($principal) {
                $this->desmarcarPrincipales($centroId, null, $usuarioRolId);
            }

            $telefono = Telefonos::create([
                'prefijo_internacional' => $this->limpiar($datos['prefijo_internacional'] ?? null),
                'numero' => trim($datos['numero']),
                'extension' => $this->limpiar($datos['extension'] ?? null),
                'observaciones' => $this->limpiar($datos['observaciones'] ?? null),
            ]);

            $relacion = EntidadesTelefonos::create([
                'centro_id' => $centroId,
                'telefono_id' => $telefono->id,
                'tipo' => trim($datos['tipo']),
                'principal' => $principal,
                'activo' => $activo,
                'observaciones' => $this->limpiar($datos['observaciones_relacion'] ?? null),
                'persona_id_created' => $usuarioRolId,
            ]);

            return $relacion->load('telefono');
        });
    }

    public function actualizarDeCentro(
        int $centroId,
        int $relacionId,
        array $datos,
        ?int $usuarioRolId = null
    ): EntidadesTelefonos {
        return DB::transaction(function () use ($centroId, $relacionId, $datos, $usuarioRolId) {
            Centros::query()->lockForUpdate()->findOrFail($centroId);

            $relacion = EntidadesTelefonos::query()
                ->where('centro_id', $centroId)
                ->whereKey($relacionId)
                ->lockForUpdate()
                ->firstOrFail();

            $telefono = Telefonos::query()
                ->lockForUpdate()
                ->findOrFail($relacion->telefono_id);

            $activo = (bool) ($datos['activo'] ?? $relacion->activo);
            $principal = $activo && (bool) ($datos['principal'] ?? $relacion->principal);

            if ($principal) {
                $this->desmarcarPrincipales($centroId, (int) $relacion->id, $usuarioRolId);
            }

            $compartido = EntidadesTelefonos::query()
                ->where('telefono_id', $telefono->id)
                ->where('id', '!=', $relacion->id)
                ->exists();

            $datosTelefono = [
                'prefijo_internacional' => $this->limpiar($datos['prefijo_internacional'] ?? null),
                'numero' => trim($datos['numero']),
                'extension' => $this->limpiar($datos['extension'] ?? null),
                'observaciones' => $this->limpiar($datos['observaciones'] ?? null),
            ];

            if ($compartido) {
                $nuevoTelefono = Telefonos::create($datosTelefono);
                $relacion->telefono_id = $nuevoTelefono->id;
            } else {
                $telefono->update($datosTelefono);
            }

            $relacion->update([
                'telefono_id' => $relacion->telefono_id,
                'tipo' => trim($datos['tipo']),
                'principal' => $principal,
                'activo' => $activo,
                'observaciones' => $this->limpiar(
                    $datos['observaciones_relacion'] ?? $relacion->observaciones
                ),
                'persona_id_updated' => $usuarioRolId,
            ]);

            return $relacion->refresh()->load('telefono');
        });
    }

    public function marcarPrincipal(
        int $centroId,
        int $relacionId,
        ?int $usuarioRolId = null
    ): EntidadesTelefonos {
        return DB::transaction(function () use ($centroId, $relacionId, $usuarioRolId) {
            Centros::query()->lockForUpdate()->findOrFail($centroId);

            $relacion = EntidadesTelefonos::query()
                ->where('centro_id', $centroId)
                ->whereKey($relacionId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$relacion->activo) {
                throw ValidationException::withMessages([
                    'principal' => 'No se puede marcar como principal un teléfono inactivo.',
                ]);
            }

            $this->desmarcarPrincipales($centroId, (int) $relacion->id, $usuarioRolId);

            $relacion->update([
                'principal' => true,
                'persona_id_updated' => $usuarioRolId,
            ]);

            return $relacion->refresh()->load('telefono');
        });
    }

    public function desvincularDeCentro(int $centroId, int $relacionId): void
    {
        DB::transaction(function () use ($centroId, $relacionId) {
            Centros::query()->lockForUpdate()->findOrFail($centroId);

            $relacion = EntidadesTelefonos::query()
                ->where('centro_id', $centroId)
                ->whereKey($relacionId)
                ->lockForUpdate()
                ->firstOrFail();

            $relacion->delete();
        });
    }

    private function desmarcarPrincipales(
        int $centroId,
        ?int $exceptId = null,
        ?int $usuarioRolId = null
    ): void {
        EntidadesTelefonos::query()
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

    private function validarPrincipal(bool $principal, bool $activo): void
    {
        if ($principal && !$activo) {
            throw ValidationException::withMessages([
                'principal' => 'El teléfono principal debe estar activo.',
            ]);
        }
    }

    private function limpiar(mixed $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }
}
