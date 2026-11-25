<?php

namespace App\Models\Contactos;

use App\Models\Personas;
use App\Models\Empresas;
use App\Models\Centros;
use App\Models\Colegios;
use App\Models\Organizaciones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntidadesTelefonos extends Model
{
    protected $table = 'entidades_telefonos';

    protected $fillable = [
        'organizacion_id',
        'empresa_id',
        'centro_id',
        'colegio_id',
        'persona_id',
        'telefono_id',
        'tipo',
        'principal',
        'activo',
        'observaciones',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function telefono(): BelongsTo
    {
        return $this->belongsTo(Telefonos::class, 'telefono_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Personas::class, 'persona_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros::class, 'centro_id');
    }

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegios::class, 'colegio_id');
    }

    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(
            Organizaciones::class,
            'organizacion_id'
        );
    }
}
