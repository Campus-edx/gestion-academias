<?php

namespace App\Models\Geografias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Municipios extends Model
{
    protected $table = 'municipios';

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincias::class, 'provincia_id');
    }
}
