<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Modelo Alumnos: lo propio de ser alumno. Su nombre y documento están en su persona.
class Alumnos extends Model
{
protected $table = 'alumnos';
protected $fillable = [
'persona_id', // A qué persona corresponde este alumno.
'observaciones',
'activo', // 1 = activo; 0 = de baja (no se borra nunca).
'persona_id_created',
'persona_id_updated',
];
protected function casts(): array
{
return [
'activo' => 'boolean', // Como sí/no (true/false).
];
}
// Un alumno pertenece a una persona: $alumno->persona->nombre
public function persona(): BelongsTo
{
return $this->belongsTo(Personas::class, 'persona_id');
}
// En Alumnos.php: sus tutores, con parentesco y permisos.
public function tutoresDelAlumno(): HasMany
{
return $this->hasMany(TutorAlumno::class, 'alumno_id');
}

}
