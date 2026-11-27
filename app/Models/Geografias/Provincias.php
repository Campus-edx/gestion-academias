<?php

namespace App\Models\Geografias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provincias extends Model
{
    protected $table = 'provincias';

    public function comunidadAutonoma(): BelongsTo
    {
        return $this->belongsTo(ComunidadesAutonomas::class, 'comunidad_autonoma_id');
    }

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipios::class, 'provincia_id');
    }
}
