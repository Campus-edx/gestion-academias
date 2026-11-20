<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo Niveles: representa la tabla "niveles" (1º Primaria ... 2º Bachillerato, Adultos).
// Catálogo que ya viene relleno en el script de la base de datos. "orden" sirve para
// mostrarlos en orden lógico (1º, 2º, 3º...) y no alfabético.
// De momento solo se lee (para el desplegable de los módulos); no tiene pantalla propia.
class Niveles extends Model
{
    protected $table = 'niveles';

    protected $fillable = [
        'nombre',
        'orden',
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