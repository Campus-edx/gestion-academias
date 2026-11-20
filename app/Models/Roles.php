<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;

// Modelo Roles: representa la tabla "roles".
// Un rol es un "puesto" en la aplicación: administrador general, responsable de cuentas,
// coordinador, logística y profesor. Ya vienen cargados en la base de datos.
// Lo que puede hacer cada rol (sus permisos) va en la tabla rol_permiso: es el capítulo de permisos.
class Roles extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'roles';

    // Campos que se pueden rellenar de golpe con create() o update().
    protected $fillable = [
        'codigo',             // Nombre interno, sin espacios: "coordinador". Es el que se usa en el código.
        'nombre',             // Nombre para mostrar: "Coordinador".
        'descripcion',
        'persona_id_created',
        'persona_id_updated',
    ];
}
