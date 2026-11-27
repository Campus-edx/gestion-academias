<?php

namespace App\Services;

use App\Models\Centros;
use App\Models\Contactos\CorreosElectronicos;
use App\Models\Contactos\EntidadesCorreos;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CorreosElectronicosService
{
    public function deCentro(int $centroId): Collection
    {
        return EntidadesCorreos::query()
            ->with('correoElectronico')
            ->where('centro_id', $centroId)
            ->orderByDesc('principal')
            ->orderBy('id')
            ->get();
    }

    public function crearParaCentro(
        int $centroId,
        array $datos,
        ?int $usuarioRolId = null
    ): EntidadesCorreos {
        return DB::transaction(function () use ($centroId, $datos, $usuarioRolId) {
            Centros::query()->lockForUpdate()->findOrFail($centroId);

            $activo = (bool) ($datos['activo'] ?? true);
            $principal = (bool) ($datos['principal'] ?? false);

            $this->validarPrincipal($principal, $activo);

            if ($principal) {
                $this->desmarcarPrincipales($centroId, null, $usuarioRolId);
            }

            $correo = CorreosElectronicos::create([
                'email' => trim($datos['email']),
                'observaciones' => $this->limpiar($datos['observaciones'] ?? null),
            ]);

            $relacion = EntidadesCorreos::create([
                'centro_id' => $centroId,
                'correo_electronico_id' => $correo->id,
                'tipo' => trim($datos['tipo']),
                'principal' => $principal,
                'activo' => $activo,
                'observaciones' => $this->limpiar($datos['observaciones_relacion'] ?? null),
                'persona_id_created' => $usuarioRolId,
            ]);

            return $relacion->load('correoElectronico');
        });
    }

    public function actualizarDeCentro(
        int $centroId,
        int $relacionId,
        array $datos,
        ?int $usuarioRolId = null
    ): EntidadesCorreos {
        return DB::transaction(function () use ($centroId, $relacionId, $datos, $usuarioRolId) {
            Centros::query()->lockForUpdate()->findOrFail($centroId);

            $relacion = EntidadesCorreos::query()
                ->where('centro_id', $centroId)
                ->whereKey($relacionId)
                ->lockForUpdate()
                ->firstOrFail();

            $correo = CorreosElectronicos::query()
                ->lockForUpdate()
                ->findOrFail($relacion->correo_electronico_id);

            $activo = (bool) ($datos['activo'] ?? $relacion->activo);
            $principal = $activo && (bool) ($datos['principal'] ?? $relacion->principal);

            if ($principal) {
                $this->desmarcarPrincipales($centroId, (int) $relacion->id, $usuarioRolId);
            }

            $compartido = EntidadesCorreos::query()
                ->where('correo_electronico_id', $correo->id)
                ->where('id', '!=', $relacion->id)
                ->exists();

            $datosCorreo = [
                'email' => trim($datos['email']),
                'observaciones' => $this->limpiar($datos['observaciones'] ?? null),
            ];

            if ($compartido) {
                $nuevoCorreo = CorreosElectronicos::create($datosCorreo);
                $relacion->correo_electronico_id = $nuevoCorreo->id;
            } else {
                $correo->update($datosCorreo);
            }

            $relacion->update([
                'correo_electronico_id' => $relacion->correo_electronico_id,
                'tipo' => trim($datos['tipo']),
                'principal' => $principal,
                'activo' => $activo,
                'observaciones' => $this->limpiar(
                    $datos['observaciones_relacion'] ?? $relacion->observaciones
                ),
                'persona_id_updated' => $usuarioRolId,
            ]);

            return $relacion->refresh()->load('correoElectronico');
        });
    }

    public function marcarPrincipal(
        int $centroId,
        int $relacionId,
        ?int $usuarioRolId = null
    ): EntidadesCorreos {
        return DB::transaction(function () use ($centroId, $relacionId, $usuarioRolId) {
            Centros::query()->lockForUpdate()->findOrFail($centroId);

            $relacion = EntidadesCorreos::query()
                ->where('centro_id', $centroId)
                ->whereKey($relacionId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$relacion->activo) {
                throw ValidationException::withMessages([
                    'principal' => 'No se puede marcar como principal un correo inactivo.',
                ]);
            }

            $this->desmarcarPrincipales($centroId, (int) $relacion->id, $usuarioRolId);

            $relacion->update([
                'principal' => true,
                'persona_id_updated' => $usuarioRolId,
            ]);

            return $relacion->refresh()->load('correoElectronico');
        });
    }

    public function desvincularDeCentro(int $centroId, int $relacionId): void
    {
        DB::transaction(function () use ($centroId, $relacionId) {
            Centros::query()->lockForUpdate()->findOrFail($centroId);

            $relacion = EntidadesCorreos::query()
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
        EntidadesCorreos::query()
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
                'principal' => 'El correo principal debe estar activo.',
            ]);
        }
    }

    private function limpiar(mixed $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }
}
