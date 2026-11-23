<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupos extends Model
{
    protected $table = 'grupos';

    protected $fillable = [
        'temporada_id',
        'curso_id',
        'centro_id',
        'plataforma_id',
        'nombre',
        'capacidad_maxima',
        'estado',
        'persona_id_created',
        'persona_id_updated',
    ];
}
