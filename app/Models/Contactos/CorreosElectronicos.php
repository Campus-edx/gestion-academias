<?php

namespace App\Models\Contactos;

use App\Models\Contactos\EntidadesCorreos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorreosElectronicos extends Model
{
    protected $table = 'correos_electronicos';

    protected $fillable = [
        'email',
        'observaciones',
    ];

    public function entidades(): HasMany
    {
        return $this->hasMany(
            EntidadesCorreos::class,
            'correo_electronico_id'
        );
    }
}
