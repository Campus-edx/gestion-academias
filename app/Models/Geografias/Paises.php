<?php

namespace App\Models\Geografias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paises extends Model
{
    protected $table = 'paises';

    public function comunidadesAutonomas(): HasMany
    {
        return $this->hasMany(ComunidadesAutonomas::class, 'pais_id');
    }
}
