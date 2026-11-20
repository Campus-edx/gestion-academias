<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

// Modelo AusenciasProfesores: representa la tabla "ausencias_profesores".
// Cada fila es un periodo en el que el profesor NO puede dar clase: vacaciones, una baja...
// La base de datos ya impide ponerle una sesión en esos días (trigger de sesion_docente).
// Jira: SCRUM-152 Registrar ausencias de profesores (HU-056).
class AusenciasProfesores extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'ausencias_profesores';

    protected $fillable = [
        'profesor_id',
        'motivo_ausencia_id', // Vacaciones, baja, asuntos personales... (catálogo motivos_ausencia).
        'fecha_inicio',
        'fecha_fin',          // Puede ser el mismo día que fecha_inicio (ausencia de un día).
        'observaciones',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    // Una ausencia es de un profesor: $ausencia->profesor
    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesores::class, 'profesor_id');
    }

    // SESIONES AFECTADAS: las clases programadas de un profesor que caen entre dos fechas.
    // Son las que habrá que pasar a otro profesor (eso lo hace la Persona 3 en sus sesiones).
    // Se mira en TODAS sus empresas, porque una ausencia es de la persona, no de una empresa.
    //   AusenciasProfesores::sesionesAfectadas(3, '2027-01-10', '2027-01-20')->get()
    // static: se usa sin tener una ausencia concreta.
    public static function sesionesAfectadas(int $profesorId, string $desde, string $hasta): Builder
    {
        return DB::table('sesiones as s')
            // sesion_docente dice qué profesor (en qué empresa) da cada sesión.
            ->join('sesion_docente as sd', 'sd.sesion_id', '=', 's.id')
            ->join('profesor_empresa as pe', 'pe.id', '=', 'sd.profesor_empresa_id')
            ->join('empresas as e', 'e.id', '=', 'pe.empresa_id')
            // leftJoin: si la sesión todavía no tiene grupo, sale igual (con el grupo vacío).
            ->leftJoin('sesion_grupo as sg', 'sg.sesion_id', '=', 's.id')
            ->leftJoin('grupos as g', 'g.id', '=', 'sg.grupo_id')
            ->leftJoin('cursos as c', 'c.id', '=', 'g.curso_id')
            ->where('pe.profesor_id', $profesorId)
            ->where('s.estado', 'programada')               // Las impartidas y canceladas no importan.
            ->whereBetween('s.fecha', [$desde, $hasta])      // fecha >= desde Y fecha <= hasta.
            // Una sesión puede juntar varios grupos: GROUP_CONCAT los pone en una sola línea, "Inglés B1 · G1, Inglés B1 · G2".
            ->groupBy('s.id', 's.fecha', 's.hora_inicio', 's.hora_fin', 'e.nombre_comercial')
            ->select([
                's.id',
                's.fecha',
                's.hora_inicio',
                's.hora_fin',
                'e.nombre_comercial as empresa',
                DB::raw("GROUP_CONCAT(CONCAT(c.nombre, ' · ', g.nombre) SEPARATOR ', ') as grupos"),
            ])
            ->orderBy('s.fecha')
            ->orderBy('s.hora_inicio');
    }
}
