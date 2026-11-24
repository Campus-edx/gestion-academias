<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Servicios de dominio futuros.
    }

    public function boot(): void
    {
        // Para cada $this->authorize('algo'), comprobamos si el usuario conectado
        // tiene ese permiso a través de alguno de sus roles vigentes.
        Gate::before(function ($user, string $ability) {
            $tienePermiso = DB::table('usuario_rol')
                ->join('rol_permiso', 'rol_permiso.rol_id', '=', 'usuario_rol.rol_id')
                ->join('permisos', 'permisos.id', '=', 'rol_permiso.permiso_id')
                ->where('usuario_rol.usuario_id', $user->id)
                ->where('usuario_rol.fecha_inicio', '<=', now()->toDateString())
                ->where(function ($q) {
                    $q->whereNull('usuario_rol.fecha_fin')
                        ->orWhere('usuario_rol.fecha_fin', '>=', now()->toDateString());
                })
                ->where('permisos.codigo', $ability)
                ->exists();

            return $tienePermiso ? true : null;
        });
    }
}