<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo Materias: representa la tabla "materias" (Inglés, Matemáticas, Lengua...).
// Catálogo, igual que CategoriasCurso. Cada módulo es de una materia.
class Materias extends Model
{
    protected $table = 'materias';

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