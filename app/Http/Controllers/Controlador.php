<?php
namespace App\Http\Controllers;
// Herramienta de Laravel para hacer consultas sencillas a la base de datos.
use Illuminate\Support\Facades\DB;
abstract class Controlador
{
// Controlador base del proyecto.
// NUEVO: id del rol con el que trabaja el usuario conectado (tabla
// usuario_rol).
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
