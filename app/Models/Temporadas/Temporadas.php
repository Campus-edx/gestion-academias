<?php

namespace App\Models\Temporadas;

use App\Models\Empresas;
use App\Models\Grupos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Temporadas extends Model
{
    protected $table = 'temporadas';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
        'persona_id_created',
        'persona_id_updated',
    ];

    // Una temporada pertenece a una empresa.
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }

    // Una temporada puede tener varios grupos.
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupos::class, 'temporada_id');
    }
}
