<?php

namespace App\Models;

use App\Models\Contactos\EntidadesDirecciones;
use App\Models\Contactos\EntidadesCorreos;
use App\Models\Contactos\EntidadesTelefonos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personas extends Model
{
    // Nombre de la tabla.
    protected $table = 'personas';

    // Campos que se pueden rellenar mediante create() o update().
    protected $fillable = [
        'nombre',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'correo_electronico',
        'telefono',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    // Una persona puede ser alumno.
    public function alumno(): HasOne
    {
        return $this->hasOne(Alumnos::class, 'persona_id');
    }

    // Una persona puede ser profesor.
    public function profesor(): HasOne
    {
        return $this->hasOne(Profesores::class, 'persona_id');
    }

    // Una persona puede tener varias direcciones.
    public function direcciones(): HasMany
    {
        return $this->hasMany(EntidadesDirecciones::class, 'persona_id');
    }

    // Una persona puede tener varios correos electrónicos.
    public function correos(): HasMany
    {
        return $this->hasMany(EntidadesCorreos::class, 'persona_id');
    }

    // Una persona puede tener varios teléfonos.
    public function telefonos(): HasMany
    {
        return $this->hasMany(EntidadesTelefonos::class, 'persona_id');
    }

    // Nombre completo.
    public function nombreCompleto(): string
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }
}
