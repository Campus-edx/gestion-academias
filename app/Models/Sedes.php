<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo Sedes: representa la tabla "sedes" (los centros de la academia).
// Hereda de Model, el modelo normal de Laravel para trabajar con una tabla.
class Sedes extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'sedes';

    // Campos que se pueden rellenar de golpe con create() o update().
    // Si un campo no está en esta lista, Laravel no lo guarda (protección).
    protected $fillable = [
        'empresa_id',          // A qué empresa pertenece la sede.
        'nombre',
        'direccion',
        'codigo_postal',
        'localidad',
        'telefono',
        'correo_electronico',
        'tipo',                // propia o externa.
        'activa',              // Si la sede está en uso.
        'persona_id_created',  // Quién la creó.
        'persona_id_updated',  // Quién la modificó por última vez.
    ];

    // Convierte automáticamente algunos campos al leerlos o guardarlos.
    protected function casts(): array
    {
        return [
            'activa' => 'boolean', // Como sí/no (true/false) en vez de 1/0.
        ];
    }

    // Relación: cada sede pertenece a una empresa (columna empresa_id).
    // Permite escribir $sede->empresa para obtener su empresa.
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }
}