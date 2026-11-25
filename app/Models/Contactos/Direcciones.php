<?php

namespace App\Models\Contactos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Direcciones extends Model
{
    protected $table = 'direcciones';

    protected $fillable = [
        'linea_direccion_1',
        'linea_direccion_2',
        'codigo_postal',
        'municipio_id',
        'observaciones',
        'requiere_revision',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'requiere_revision' => 'boolean',
        ];
    }

    // Una dirección puede estar asociada a distintas entidades.
    public function entidades(): HasMany
    {
        return $this->hasMany(
            EntidadesDirecciones::class,
            'direccion_id'
        );
    }
}
