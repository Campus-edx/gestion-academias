<?php

namespace App\Models;

use App\Models\Contactos\EntidadesDirecciones;
use App\Models\Contactos\EntidadesCorreos;
use App\Models\Contactos\EntidadesTelefonos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Centros extends Model
{
    protected $table = 'centros';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'activo',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }


    //............................. Empresa a la que pertenece el centro ........................................
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }


    //............................. Direcciones asociadas al centro ........................................
    public function direcciones(): HasMany
    {
        return $this->hasMany(
            EntidadesDirecciones::class,
            'centro_id'
        );
    }


    //............................. Correos electrónicos asociados al centro ........................................
    public function correos(): HasMany
    {
        return $this->hasMany(
            EntidadesCorreos::class,
            'centro_id'
        );
    }

    //............................. Teléfonos asociados al centro ........................................
    public function telefonos(): HasMany
    {
        return $this->hasMany(
            EntidadesTelefonos::class,
            'centro_id'
        );
    }


    //............................. Grupos asociados al centro ........................................
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupos::class, 'centro_id');
    }
}
