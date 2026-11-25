<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo Parentescos: representa la tabla "parentescos" (Padre, Madre, Tutor/a legal, Otro).
// Catálogo que ya viene relleno en el script de la base de datos. De momento solo se lee.
class Parentescos extends Model
{
    protected $table = 'parentescos';

    protected $fillable = [
        'codigo',
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
