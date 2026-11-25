<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo CursoEquipamiento: representa la tabla "curso_equipamiento".
// Lo que necesita el aula de un curso: "12 ordenadores", "1 proyector".
// Lo usa la búsqueda de aulas disponibles de la Persona 3.
class CursoEquipamiento extends Model
{
    protected $table = 'curso_equipamiento';

    protected $fillable = [
        'curso_id',
        'equipamiento_id',    // Del catálogo equipamientos (ya viene relleno).
        'cantidad',           // Cuántas unidades hacen falta.
        'persona_id_created',
        'persona_id_updated',
    ];
}
