<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo Modulos: representa la tabla "modulos" (Inglés B1, Matemáticas 2º ESO...).
// Un módulo es un contenido; un curso se compone de uno o varios módulos (tabla curso_modulo).
// Lo crea una empresa (empresa_id) y lo pueden usar los cursos de todas.
class Modulos extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'modulos';

    protected $fillable = [
        'empresa_id',         // Empresa que lo creó.
        'materia_id',         // Inglés, Matemáticas... (catálogo materias). Obligatorio.
        'nivel_id',           // 1º ESO, Adultos... (catálogo niveles). Opcional.
        'nombre',
        'descripcion',
        'activo',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // La materia del módulo: $modulo->materia->nombre
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materias::class, 'materia_id');
    }

    // El nivel del módulo (puede no tener): $modulo->nivel?->nombre
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Niveles::class, 'nivel_id');
    }

    // La empresa que lo creó.
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }
}
