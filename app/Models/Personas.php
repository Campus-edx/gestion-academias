<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
// Modelo Personas: datos personales de CUALQUIER persona (alumno, tutor,
// profesor...).
class Personas extends Model
{
// Nombre de la tabla (lo escribimos siempre, como pide la guía).
protected $table = 'personas';
// Campos que se pueden rellenar de golpe con create() o update().
protected $fillable = [
'nombre',
'apellidos',
'tipo_documento', // dni, nie o pasaporte (puede ir vacío).
'numero_documento', // Va junto con el tipo: los dos rellenos o los dos vacíos.
'fecha_nacimiento',
'correo_electronico',
'telefono',
'persona_id_created', // Quién creó el registro (id de usuario_rol).
'persona_id_updated', // Quién lo modificó por última vez.
];
// La fecha de nacimiento se lee como fecha: se puede escribir -
// >format('d/m/Y').
protected function casts(): array
{
return [
'fecha_nacimiento' => 'date',
];
}
// Una persona puede ser alumno (como mucho una vez): $persona->alumno.
public function alumno(): HasOne
{
return $this->hasOne(Alumnos::class, 'persona_id');
}
// Nombre completo: $persona->nombreCompleto()
public function nombreCompleto(): string
{
return trim($this->nombre . ' ' . $this->apellidos);
}
// Relación: una persona puede ser profesor (como mucho una vez).
// Permite escribir $persona->profesor (o null si no lo es).
public function profesor(): HasOne
{
return $this->hasOne(Profesores::class, 'persona_id');
}
// En Personas.php: una persona puede ser tutor.
public function tutor(): HasOne
{
return $this->hasOne(Tutores::class, 'persona_id');
}

}