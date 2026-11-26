<?php
// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models\Tarifas;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// Modelo Tarifas: representa la tabla "tarifas".
// Una tarifa es un PRECIO con su IVA, su periodicidad y desde qué fecha vale.
// Es de UNA de estas dos cosas (la base de datos no deja las dos ni ninguna):
//   · curso_id        → precio del curso completo (lo usa inscripciones.tarifa_id)
//   · curso_modulo_id → precio de un módulo suelto del curso (lo usa inscripcion_modulo.tarifa_id)
// Una tarifa NO se modifica: cuando cambia el precio se CIERRA (vigente_hasta) y se crea otra.
// Así las facturas antiguas siguen cuadrando con el precio que tenían.
// Jira: SCRUM-354 Gestionar tarifas (355 a 358).
class Tarifas extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'tarifas';

    protected $fillable = [
        'curso_id',           // Rellena SOLO si es del curso completo.
        'curso_modulo_id',    // Rellena SOLO si es de un módulo suelto.
        'nombre',             // "Mensualidad 2026-27", "Curso completo"...
        'importe',            // Sin IVA.
        'periodicidad',       // mensual, trimestral, anual o curso_completo.
        'iva_porcentaje',     // 21.00, 0.00...
        'vigente_desde',
        'vigente_hasta',      // Vacía = sigue vigente.
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            // decimal:2 → se lee siempre con 2 decimales ("45.50"), como en la base de datos.
            'importe' => 'decimal:2',
            'iva_porcentaje' => 'decimal:2',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
        ];
    }

    // Los nombres de las periodicidades, para desplegables y listados.
    public const PERIODICIDADES = [
        'mensual' => 'Mensual',
        'trimestral' => 'Trimestral',
        'anual' => 'Anual',
        'curso_completo' => 'Curso completo (un pago)',
    ];

    // SCOPE "vigentesEn": las tarifas que valen en una fecha (hoy si no se dice).
    // Es lo que usará la Persona 2 al matricular (SCRUM-358) para proponer la tarifa del curso:
    //   Tarifas::query()->where('curso_id', $grupo->curso_id)->vigentesEn($inscripcion->fecha_alta)->get()
    public function scopeVigentesEn(Builder $consulta, $fecha = null): Builder
    {
        $fecha = $fecha ? (string) $fecha : now()->toDateString();

        return $consulta
            ->where('vigente_desde', '<=', $fecha)
            ->where(fn ($q) => $q->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', $fecha));
    }

    // ¿Vale hoy? Para la etiqueta del listado.
    public function estaVigente(): bool
    {
        return $this->vigente_desde->lte(today())
            && ($this->vigente_hasta === null || $this->vigente_hasta->gte(today()));
    }

    // Importe con IVA, calculado (no se guarda): 100 + 21 % = 121.00
    public function importeConIva(): float
    {
        return round((float) $this->importe * (1 + (float) $this->iva_porcentaje / 100), 2);
    }
}

