<?php

namespace App\Models\Geografias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComunidadesAutonomas extends Model
{
    protected $table = 'comunidades_autonomas';

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Paises::class, 'pais_id');
    }

    public function provincias(): HasMany
    {
        return $this->hasMany(Provincias::class, 'comunidad_autonoma_id');
    }
}
