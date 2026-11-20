<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Modelos que usa.
use App\Models\DisponibilidadProfesores;
use App\Models\Profesores;
// Piezas de Laravel.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Controlador de la DISPONIBILIDAD HORARIA de un profesor (tabla disponibilidad_profesores).
// Jira: SCRUM-250 Gestionar disponibilidad horaria.
// Se usa desde la ficha del profesor: añadir una franja y quitarla.
class DisponibilidadProfesoresController extends Controlador
{
    // AÑADIR una franja (ruta POST /profesores/{profesor}/disponibilidad).
    public function store(Request $request, Profesores $profesor): RedirectResponse
    {
        $datos = $request->validateWithBag('disponibilidad', [
            // between:1,7 = un número del 1 (lunes) al 7 (domingo).
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            // date_format:H:i = una hora como 17:00 (es lo que envía un campo type="time").
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            // Fechas opcionales: si se dejan vacías, la franja vale siempre.
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ], [
            'dia_semana.required' => 'Elige el día.',
            'hora_inicio.required' => 'Indica la hora de inicio.',
            'hora_fin.required' => 'Indica la hora de fin.',
            'hora_fin.after' => 'La hora de fin tiene que ser posterior a la de inicio.',
            'hasta.after_or_equal' => 'La fecha "hasta" no puede ser anterior a "desde".',
        ]);

        // Regla que no comprueba la base de datos: dos franjas del mismo día no pueden pisarse
        // (en horas y en fechas). Las fechas vacías cuentan como "sin límite".
        $desde = $datos['desde'] ?? null;
        $hasta = $datos['hasta'] ?? null;

        $choca = DisponibilidadProfesores::query()
            ->where('profesor_id', $profesor->id)
            ->where('dia_semana', $datos['dia_semana'])
            // Las horas se solapan: una empieza antes de que acabe la otra, y acaba después de que empiece.
            ->where('hora_inicio', '<', $datos['hora_fin'])
            ->where('hora_fin', '>', $datos['hora_inicio'])
            // Y las fechas también se cruzan.
            ->when($hasta, fn ($q) => $q->where(fn ($w) => $w->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $hasta)))
            ->when($desde, fn ($q) => $q->where(fn ($w) => $w->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $desde)))
            ->exists();

        if ($choca) {
            return back()->withInput()->withErrors([
                'hora_inicio' => 'Esa franja se pisa con otra del mismo día. Quita la otra o cambia las horas.',
            ], 'disponibilidad');
        }

        DisponibilidadProfesores::create([
            'profesor_id' => $profesor->id,
            'dia_semana' => $datos['dia_semana'],
            'hora_inicio' => $datos['hora_inicio'],
            'hora_fin' => $datos['hora_fin'],
            'fecha_inicio' => $desde,
            'fecha_fin' => $hasta,
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('profesores.edit', $profesor)->with('success', 'Franja de disponibilidad añadida.');
    }

    // QUITAR una franja (ruta DELETE /profesores/{profesor}/disponibilidad/{disponibilidad}).
    // Aquí sí se borra: una franja de disponibilidad no es un dato histórico.
    public function destroy(Profesores $profesor, DisponibilidadProfesores $disponibilidad): RedirectResponse
    {
        // Seguridad: la franja tiene que ser de ESTE profesor.
        abort_if((int) $disponibilidad->profesor_id !== (int) $profesor->id, 404);

        $disponibilidad->delete();

        return redirect()->route('profesores.edit', $profesor)->with('success', 'Franja quitada.');
    }
}
