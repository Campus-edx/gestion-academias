<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Pieza de Laravel que añade $this->authorize() a los controladores.
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// Herramienta de Laravel para hacer consultas sencillas a la base de datos.
use Illuminate\Support\Facades\DB;

// Controlador base del proyecto: todos los controladores heredan de este ("extends Controlador").
// Lo que se pone aquí lo pueden usar todos.
abstract class Controlador
{
    // NUEVO (capítulo de permisos): con esta línea, cualquier controlador puede escribir
    //   $this->authorize('alumnos.editar');
    // Si el usuario no tiene ese permiso, Laravel corta ahí y enseña la página 403.
    // "use" DENTRO de una clase no es lo mismo que arriba: aquí copia dentro de la clase
    // las funciones de AuthorizesRequests (es un "trait", un trozo de clase reutilizable).
    use AuthorizesRequests;

    // Id del rol con el que trabaja el usuario conectado (tabla usuario_rol).
    // Es lo que se guarda en persona_id_created y persona_id_updated.
    // Busca su primer rol vigente: ya empezado y sin terminar.
    // Devuelve null si el usuario no tiene ningún rol vigente.
    protected function usuarioRolId(): ?int
    {
        $hoy = now()->toDateString();

        return DB::table('usuario_rol')
            ->where('usuario_id', auth()->id())
            ->where('fecha_inicio', '<=', $hoy)
            ->where(fn ($consulta) => $consulta->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy))
            ->orderBy('id')
            ->value('id');
    }
}