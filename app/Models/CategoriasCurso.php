<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo CategoriasCurso: representa la tabla "categorias_curso" (Idiomas, Refuerzo escolar...).
// Es un CATÁLOGO: una lista de valores que se eligen en un desplegable. No se borra: se desactiva.
class CategoriasCurso extends Model
{
    protected $table = 'categorias_curso';

    protected $fillable = [
        'nombre',
        'activo',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }
}
