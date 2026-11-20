<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;

// Modelo DisponibilidadProfesores: representa la tabla "disponibilidad_profesores".
// Cada fila es una franja de la semana en la que el profesor PUEDE dar clase:
// "los lunes de 17:00 a 20:00". Opcionalmente, solo entre dos fechas.
// La usa la Persona 3 para elegir profesor al programar sesiones.
class DisponibilidadProfesores extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'disponibilidad_profesores';

    protected $fillable = [
        'profesor_id',
        'dia_semana',         // 1 = lunes … 7 = domingo.
        'hora_inicio',
        'hora_fin',
        'fecha_inicio',       // Opcional: desde cuándo vale esta franja.
        'fecha_fin',          // Opcional: hasta cuándo.
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

    // Nombre del día para mostrarlo: $franja->nombreDia() → "Lunes".
    public function nombreDia(): string
    {
        return [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
                5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'][$this->dia_semana] ?? '';
    }
}
