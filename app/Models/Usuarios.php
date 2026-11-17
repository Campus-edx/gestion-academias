<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Modelo Usuarios: representa la tabla "usuarios".
// Hereda de Authenticatable (no de Model) para poder iniciar sesión con él.
class Usuarios extends Authenticatable
{
    // Permite enviarle notificaciones (por ejemplo, correos).
    use Notifiable;

    // Nombre de la tabla en la base de datos.
    protected $table = 'usuarios';

    // Columna donde se guarda el código de "Recordarme" (en castellano en vez de remember_token).
    protected $rememberTokenName = 'token_recuerdo';

    // Campos que se pueden rellenar de golpe con create() o update().
    protected $fillable = [
        'empresa_id',
        'nombre',
        'correo_electronico',
        'contrasena',
        'activo',
        'fecha_ultimo_acceso',
        'persona_id_created',
        'persona_id_updated',
    ];

    // Campos que nunca se muestran al convertir el usuario en datos (por seguridad).
    protected $hidden = [
        'contrasena',
        'token_recuerdo',
    ];

    // Convierte automáticamente algunos campos al leerlos o guardarlos.
    protected function casts(): array
    {
        return [
            'fecha_verificacion_correo' => 'datetime', // Como fecha y hora.
            'fecha_ultimo_acceso' => 'datetime',       // Como fecha y hora.
            'activo' => 'boolean',                     // Como sí/no (true/false).
            'contrasena' => 'hashed',                  // Se cifra sola al guardarla.
        ];
    }

    // Relación: cada usuario pertenece a una empresa (columna empresa_id).
    // Permite escribir $usuario->empresa para obtener su empresa.
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }
}