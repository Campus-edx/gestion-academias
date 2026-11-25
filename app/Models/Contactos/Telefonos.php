<?php

namespace App\Models\Contactos;

use App\Models\Contactos\EntidadesTelefonos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Telefonos extends Model
{
    protected $table = 'telefonos';

    protected $fillable = [
        'prefijo_internacional',
        'numero',
        'extension',
        'observaciones',
    ];

    public function entidades(): HasMany
    {
        return $this->hasMany(
            EntidadesTelefonos::class,
            'telefono_id'
        );
    }
}
