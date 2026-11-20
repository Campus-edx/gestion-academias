<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo Profesores: representa la tabla "profesores".
// Igual que un alumno, el nombre y el documento están en su persona (tabla personas).
// Un profesor es el mismo para toda la organización, aunque trabaje para varias empresas.
class Profesores extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'profesores';

    // Campos que se pueden rellenar de golpe con create() o update().
    protected $fillable = [
        'persona_id',         // A qué persona corresponde este profesor.
        'especialidad',       // Texto libre: "Inglés y francés", "Matemáticas"...
        'iban',               // Cuenta bancaria para pagarle.
        'activo',             // 1 = activo; 0 = de baja (no se borra nunca).
        'persona_id_created',
        'persona_id_updated',
    ];

    // Convierte automáticamente algunos campos al leerlos o guardarlos.
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // Un profesor pertenece a una persona: $profesor->persona->nombre
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Personas::class, 'persona_id');
    }

    // Sus vinculaciones con empresas, actuales y antiguas (tabla profesor_empresa).
    // $profesor->vinculaciones
    public function vinculaciones(): HasMany
    {
        return $this->hasMany(ProfesorEmpresa::class, 'profesor_id');
    }

    // Solo las vinculaciones vigentes hoy. Usa el "scope" vigentes() del modelo ProfesorEmpresa.
    // $profesor->vinculacionesVigentes
    public function vinculacionesVigentes(): HasMany
    {
        return $this->hasMany(ProfesorEmpresa::class, 'profesor_id')->vigentes();
    }

    // Sus franjas de disponibilidad horaria (tabla disponibilidad_profesores).
    // $profesor->disponibilidades
    public function disponibilidades(): HasMany
    {
        return $this->hasMany(DisponibilidadProfesores::class, 'profesor_id');
    }

    // NUEVO (ausencias): sus vacaciones, bajas y otras ausencias (tabla ausencias_profesores).
    // $profesor->ausencias
    public function ausencias(): HasMany
    {
        return $this->hasMany(AusenciasProfesores::class, 'profesor_id');
    }
}
