<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros::class, 'centro_id');
    }
}
