<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo TutorAlumno: representa la tabla "tutor_alumno".
// Cada fila une un tutor con un alumno: qué parentesco tienen, si es el contacto principal
// del alumno y si está autorizado a recogerlo. Un alumno puede tener varios tutores
// y un tutor varios alumnos (hermanos). El mismo par tutor-alumno solo una vez.
class TutorAlumno extends Model
{
    protected $table = 'tutor_alumno';

    protected $fillable = [
        'tutor_id',
        'alumno_id',
        'parentesco_id',          // Padre, madre, tutor legal, otro (catálogo parentescos).
        'es_contacto_principal',  // A quién se llama primero. Uno por alumno.
        'autorizado_recogida',    // Si puede recoger al alumno.
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'es_contacto_principal' => 'boolean',
            'autorizado_recogida' => 'boolean',
        ];
    }

    // El tutor: $relacion->tutor->persona->nombre
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutores::class, 'tutor_id');
    }

    // El alumno: $relacion->alumno->persona->nombre
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'alumno_id');
    }

    // El parentesco: $relacion->parentesco->nombre
    public function parentesco(): BelongsTo
    {
        return $this->belongsTo(Parentescos::class, 'parentesco_id');
    }
}
