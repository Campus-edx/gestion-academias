<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo ProfesorEmpresa: representa la tabla "profesor_empresa".
// Cada fila es una VINCULACIÓN de un profesor con una empresa durante un periodo:
// si es empleado o autónomo, cuánto cobra por hora y su IRPF.
// Vigente = fecha_fin vacía o de hoy en adelante. Para cambiar algo (por ejemplo, de autónomo
// a empleado) NO se modifica la fila: se cierra (fecha_fin) y se crea otra. Así queda el historial.
// (El nombre del modelo sigue al de la tabla, que une dos cosas: profesor + empresa.)
class ProfesorEmpresa extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'profesor_empresa';

    protected $fillable = [
        'profesor_id',
        'empresa_id',
        'tipo_vinculacion',   // 'empleado' o 'autonomo'.
        'precio_hora',        // Lo usa la Persona 1 para las liquidaciones.
        'irpf_porcentaje',    // Retención de IRPF (normalmente de los autónomos).
        'fecha_inicio',
        'fecha_fin',          // Vacía = sigue vigente.
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'precio_hora' => 'decimal:2',
            'irpf_porcentaje' => 'decimal:2',
        ];
    }

    // La empresa de esta vinculación: $vinculacion->empresa->razon_social
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }

    // El profesor de esta vinculación.
    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesores::class, 'profesor_id');
    }

    // "Scope": un filtro con nombre que se puede añadir a cualquier consulta.
    // ProfesorEmpresa::query()->vigentes() = solo las que valen hoy:
    // ya empezadas (fecha_inicio hoy o antes) y sin terminar (fecha_fin vacía o de hoy en adelante).
    public function scopeVigentes(Builder $consulta): Builder
    {
        $hoy = now()->toDateString();

        return $consulta->where('fecha_inicio', '<=', $hoy)
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy));
    }

    // true si la vinculación vale hoy (para pintar "Vigente" o "Cerrada" en la ficha).
    public function estaVigente(): bool
    {
        $hoy = now()->startOfDay();

        return $this->fecha_inicio <= $hoy && ($this->fecha_fin === null || $this->fecha_fin >= $hoy);
    }
}
