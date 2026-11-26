<?php

namespace App\Providers;

// Modelos y piezas de Laravel que usa.
use App\Models\Usuarios; // nuevo de SCRUM-187

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
        // Un solo Gate para TODOS los permisos (usuarios, empresas, organización, ...).
        // Gate::before se ejecuta antes de cualquier authorize()/@can/->can().
        //
        // $permiso  = el código que se pregunta, p. ej. 'usuarios.editar' o 'empresas.ver'.
        // $argumentos[0] = centro_id opcional. Si se pasa, vale un rol de ese centro o uno global (centro_id null).
        // Si no se pasa (caso actual de usuarios/empresas), se comprueba el permiso en cualquier rol vigente.
        Gate::before(function (Usuarios $usuario, string $permiso, array $argumentos = []) {
            $centroId = $argumentos[0] ?? null;

            $tienePermiso = DB::table('v_usuarios_permisos')
                ->where('usuario_id', $usuario->id)
                ->where('permiso', $permiso)
                ->when($centroId, fn ($consulta) => $consulta->where(fn ($donde) => $donde
                    ->whereNull('centro_id')
                    ->orWhere('centro_id', $centroId)))
                ->exists();

            // true = puede. null = "no lo sé"; sin más reglas, Laravel lo toma como NO (403).
            return $tienePermiso ? true : null;
        });

      /*  // Para cada $this->authorize('algo'), comprobamos si el usuario conectado
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
        }); */
    }
}