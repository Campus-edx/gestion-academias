<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo Organizaciones: representa la tabla "organizaciones".
// La organización es el grupo que está por encima de todas las empresas.
// En la práctica hay UNA (la organización central), pero la tabla admite más.
class Organizaciones extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'organizaciones';

    protected $fillable = [
        'nombre',
        'cif',                // Único: dos organizaciones no pueden tener el mismo CIF.
        'correo_electronico',
        'telefono',
        'persona_id_created',
        'persona_id_updated',
    ];

    // Las empresas de la organización: $organizacion->empresas
    public function empresas(): HasMany
    {
        return $this->hasMany(Empresas::class, 'organizacion_id');
    }
}