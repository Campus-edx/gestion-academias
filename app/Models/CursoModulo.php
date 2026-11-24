<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo CursoModulo: representa la tabla "curso_modulo".
// Cada fila dice que un módulo forma parte de un curso, en qué orden y con cuántas horas.
// Un mismo módulo no puede estar dos veces en el mismo curso.
// OJO: otras tablas apuntan aquí (tarifas por módulo, matrículas de módulos sueltos,
// sesiones y asignaciones de profesores). Si ya se usa, la base de datos no deja quitarlo.
class CursoModulo extends Model
{
    protected $table = 'curso_modulo';

    protected $fillable = [
        'curso_id',
        'modulo_id',
        'orden',              // 1, 2, 3... en qué orden se da.
        'horas',              // Horas de ese módulo dentro del curso.
        'persona_id_created',
        'persona_id_updated',
    ];

    // El módulo: $cursoModulo->modulo->nombre
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulos::class, 'modulo_id');
    }
}
