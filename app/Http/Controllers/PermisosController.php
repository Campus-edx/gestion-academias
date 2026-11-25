<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Modelos que usa esta pantalla.
use App\Models\Permisos;
use App\Models\Roles;
use App\Models\UsuarioRol;
// Piezas de Laravel.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Controlador de la MATRIZ DE PERMISOS: qué permisos tiene cada rol (tabla rol_permiso).
// Jira: SCRUM-187 Gestionar roles y permisos (subtareas 188, 189 y 191).
// Una sola pantalla: una fila por permiso, una columna por rol y una casilla en cada cruce.
// Solo entra quien tiene el permiso 'permisos.editar'.
class PermisosController extends Controlador
{
    // VER LA MATRIZ (ruta GET /permisos).
    public function index(): View
    {
        // Si no tiene el permiso, Laravel corta aquí con la página 403.
        $this->authorize('permisos.editar');

        return view('permisos.index', [
            'roles' => Roles::query()->orderBy('id')->get(),
            // groupBy('modulo'): los agrupa por módulo para pintar un bloque por módulo
            // ('alumnos' => [alumnos.ver, alumnos.editar], 'grupos' => [...], ...).
            'permisosPorModulo' => Permisos::query()->orderBy('modulo')->orderBy('codigo')->get()->groupBy('modulo'),
            // Lo que está marcado ahora, como texto "rol-permiso" ("3-12"), para preguntar rápido
            // en la vista si una casilla va marcada: in_array("3-12", $marcados).
            'marcados' => DB::table('rol_permiso')->get()
                ->map(fn ($fila) => $fila->rol_id . '-' . $fila->permiso_id)
                ->all(),
        ]);
    }

    // GUARDAR LA MATRIZ (ruta PUT /permisos).
    public function update(Request $request): RedirectResponse
    {
        $this->authorize('permisos.editar');

        // Llega así: permisos[3][] = 12, permisos[3][] = 15, permisos[5][] = 12...
        // es decir, [id del rol => [ids de los permisos marcados]].
        // Una casilla desmarcada no se envía: un rol sin nada marcado no aparece.
        $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['array'],
            'permisos.*.*' => ['integer', 'exists:permisos,id'],
        ]);

        // Lo que ha llegado, como lista de [rol, permiso] (solo de roles que existen).
        $idsRoles = Roles::query()->pluck('id')->all();
        $nuevos = [];
        foreach ((array) $request->input('permisos', []) as $rolId => $permisosDelRol) {
            if (! in_array((int) $rolId, $idsRoles, true)) {
                continue; // Un rol que no existe: se ignora.
            }
            foreach ($permisosDelRol as $permisoId) {
                $nuevos[] = (int) $rolId . '-' . (int) $permisoId;
            }
        }
        $nuevos = array_unique($nuevos);

        // SEGURIDAD: nadie puede quitarse a sí mismo el permiso de cambiar permisos.
        // Si lo hiciera, nadie podría volver a entrar en esta pantalla para arreglarlo.
        // Se mira si alguno de MIS roles vigentes sigue teniendo 'permisos.editar' en lo nuevo.
        $permisoEditar = Permisos::query()->where('codigo', 'permisos.editar')->value('id');
        $misRoles = UsuarioRol::query()->where('usuario_id', $request->user()->id)->vigentes()->pluck('rol_id');
        $loConservo = $misRoles->contains(fn ($rolId) => in_array($rolId . '-' . $permisoEditar, $nuevos, true));

        if (! $loConservo) {
            return back()->withErrors([
                'permisos' => 'No puedes quitarte el permiso "Cambiar los permisos de cada rol": '
                    . 'nadie podría volver a entrar aquí. Déjalo marcado en alguno de tus roles.',
            ]);
        }

        // Lo que hay ahora en la base de datos, con el mismo formato "rol-permiso".
        $actuales = DB::table('rol_permiso')->get()
            ->mapWithKeys(fn ($fila) => [$fila->rol_id . '-' . $fila->permiso_id => $fila->id]);

        // DB::transaction: o se guarda todo, o nada (si algo falla a mitad, se deshace).
        DB::transaction(function () use ($nuevos, $actuales) {
            // 1. Borrar los que estaban marcados y ya no.
            //    (Aquí sí se borra: rol_permiso es una configuración, no un dato histórico).
            $sobran = $actuales->except($nuevos);
            if ($sobran->isNotEmpty()) {
                DB::table('rol_permiso')->whereIn('id', $sobran->values())->delete();
            }

            // 2. Añadir los nuevos. Los que siguen marcados no se tocan.
            foreach (array_diff($nuevos, $actuales->keys()->all()) as $clave) {
                [$rolId, $permisoId] = explode('-', $clave);
                DB::table('rol_permiso')->insert([
                    'rol_id' => $rolId,
                    'permiso_id' => $permisoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'persona_id_created' => $this->usuarioRolId(),
                ]);
            }
        });

        return redirect()->route('permisos.index')->with('success', 'Permisos guardados. Se aplican desde ya a todos los usuarios.');
    }
}
