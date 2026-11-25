<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo Tutores: representa la tabla "tutores" (padres, madres o tutores legales).
// La tabla solo guarda a qué persona corresponde: todos sus datos están en personas.
// No tiene casilla "activo": un tutor deja de serlo cuando ya no tiene alumnos a su cargo.
class Tutores extends Model
{
    protected $table = 'tutores';

    protected $fillable = [
        'persona_id',
        'persona_id_created',
        'persona_id_updated',
    ];

    // Un tutor pertenece a una persona: $tutor->persona->nombre
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Personas::class, 'persona_id');
    }

    // Los alumnos a su cargo, con parentesco y permisos (tabla tutor_alumno): $tutor->alumnosACargo
    public function alumnosACargo(): HasMany
    {
        return $this->hasMany(TutorAlumno::class, 'tutor_id');
    }
}
