<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models/Inscripciones.
namespace App\Models\Inscripciones;

// Piezas de Laravel que usa este modelo.
use App\Models\Alumnos;
use App\Models\Personas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

// Modelo Inscripciones: representa la tabla "inscripciones" (la matrícula de un alumno en un grupo).
// Guarda quién paga (pagador_persona_id: el alumno o uno de sus tutores), desde cuándo (fecha_alta)
// y, si se da de baja, hasta cuándo (fecha_baja). No se borra nunca: se da de baja.
// Estados: activa · baja · finalizada (la pone el grupo al terminar) · anulada (matrícula hecha por error:
// no cuenta para nada). Un alumno puede tener varias en el mismo grupo (si se fue y volvió), pero solo UNA activa.
// La base de datos comprueba, al guardar, que los alumnos matriculados caben en el aula de sus clases.
class Inscripciones extends Model
{
    protected $table = 'inscripciones';

    protected $fillable = [
        'alumno_id',
        'grupo_id',
        'solicitud_id',         // Si viene de una solicitud previa (opcional).
        'pagador_persona_id',   // Quién paga: la persona del alumno o la de uno de sus tutores.
        'tarifa_id',            // Precio (lo gestiona la Persona 1). De momento puede ir vacío.
        'descuento_id',
        'fecha_alta',           // Fecha de matriculación.
        'fecha_baja',           // Vacía = sigue matriculado.
        'estado',               // activa, baja o finalizada.
        'observaciones',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'fecha_alta' => 'date',
            'fecha_baja' => 'date',
        ];
    }

    // El alumno matriculado.
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'alumno_id');
    }

    // Quién paga: $inscripcion->pagador->nombreCompleto()
    public function pagador(): BelongsTo
    {
        return $this->belongsTo(Personas::class, 'pagador_persona_id');
    }

    // Consulta del HISTORIAL de inscripciones, con todo lo que hay que enseñar en una fila:
    // alumno, grupo, curso, centro, empresa y pagador. Se usa en el listado y en la ficha del alumno.
    // Los grupos y los centros son de la Persona 3: se unen con join, sin usar sus modelos.
    // Devuelve una consulta sin terminar: quien la usa añade sus filtros y llama a ->get().
    public static function historial(): Builder
    {
        return DB::table('inscripciones as i')
            ->join('alumnos as a', 'a.id', '=', 'i.alumno_id')
            ->join('personas as p', 'p.id', '=', 'a.persona_id')
            ->join('grupos as g', 'g.id', '=', 'i.grupo_id')
            ->join('cursos as c', 'c.id', '=', 'g.curso_id')
            ->join('centros as ce', 'ce.id', '=', 'g.centro_id')
            ->join('empresas as e', 'e.id', '=', 'ce.empresa_id')
            ->join('personas as pag', 'pag.id', '=', 'i.pagador_persona_id')
            ->select([
                'i.id', 'i.alumno_id', 'i.grupo_id', 'i.estado', 'i.fecha_alta', 'i.fecha_baja',
                'p.nombre as alumno_nombre', 'p.apellidos as alumno_apellidos',
                'g.nombre as grupo', 'g.fecha_inicio as grupo_inicio', 'g.fecha_fin as grupo_fin',
                'c.id as curso_id', 'c.nombre as curso', 'ce.nombre as centro',
                'e.id as empresa_id', 'e.nombre_comercial as empresa',
                'pag.nombre as pagador_nombre', 'pag.apellidos as pagador_apellidos',
            ])
            // Primero las más recientes.
            ->orderByDesc('i.fecha_alta');
    }
}
