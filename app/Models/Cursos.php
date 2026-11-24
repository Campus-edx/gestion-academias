<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo Cursos: representa la tabla "cursos" (la oferta de cada empresa: "Inglés B1 tarde").
// Un curso NO tiene fechas: las fechas son de cada grupo (Persona 3).
// Se compone de módulos (curso_modulo) y puede pedir un tipo de aula y un equipamiento.
class Cursos extends Model
{
    protected $table = 'cursos';

    protected $fillable = [
        'empresa_id',          // Empresa que ofrece el curso.
        'categoria_curso_id',  // Idiomas, Refuerzo... Obligatoria.
        'tipo_espacio_id',     // Tipo de aula que necesita (informática...). Vacío = cualquiera.
        'nombre',
        'descripcion',
        'modalidad',           // presencial, online o mixta.
        'edad_minima',         // Opcionales.
        'edad_maxima',
        'min_alumnos',         // Mínimo para abrir un grupo.
        'max_alumnos',         // Máximo por grupo.
        'activo',              // 0 = ya no se ofrece (no se borra).
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // Su categoría: $curso->categoria->nombre
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriasCurso::class, 'categoria_curso_id');
    }

    // Sus módulos, con orden y horas (tabla curso_modulo): $curso->modulosDelCurso
    public function modulosDelCurso(): HasMany
    {
        return $this->hasMany(CursoModulo::class, 'curso_id');
    }

    // El equipamiento que necesita su aula (tabla curso_equipamiento).
    public function equipamientoNecesario(): HasMany
    {
        return $this->hasMany(CursoEquipamiento::class, 'curso_id');
    }
}
