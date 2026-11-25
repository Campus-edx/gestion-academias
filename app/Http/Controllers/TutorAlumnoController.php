<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

use App\Models\Alumnos;
use App\Models\TutorAlumno;
use App\Models\Tutores;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Controlador de la RELACIÓN TUTOR-ALUMNO (tabla tutor_alumno), desde la ficha del tutor.
// Jira: SCRUM-215 Asociar tutores y alumnos (varios tutores por alumno, varios alumnos por tutor,
// con su parentesco).
class TutorAlumnoController extends Controlador
{
    // ASOCIAR un alumno al tutor (ruta POST /tutores/{tutor}/alumnos).
    public function store(Request $request, Tutores $tutor): RedirectResponse
    {
        $datos = $request->validateWithBag('asociar', [
            'alumno_id' => ['required', 'integer', 'exists:alumnos,id'],
            'parentesco_id' => ['required', 'integer', 'exists:parentescos,id'],
        ], [
            'alumno_id.required' => 'Elige el alumno.',
            'parentesco_id.required' => 'Elige el parentesco.',
        ]);

        $alumno = Alumnos::findOrFail($datos['alumno_id']);

        // Una persona no puede ser tutora de sí misma (por ejemplo, un alumno adulto que también es tutor).
        if ((int) $alumno->persona_id === (int) $tutor->persona_id) {
            return back()->withInput()->withErrors(['alumno_id' => 'Una persona no puede ser tutora de sí misma.'], 'asociar');
        }

        // El mismo tutor y el mismo alumno, solo una vez (la base de datos tampoco lo deja).
        if ($tutor->alumnosACargo()->where('alumno_id', $alumno->id)->exists()) {
            return back()->withInput()->withErrors(['alumno_id' => 'Ese alumno ya está asociado a este tutor.'], 'asociar');
        }

        $esPrincipal = $request->boolean('es_contacto_principal');

        DB::transaction(function () use ($tutor, $alumno, $datos, $esPrincipal, $request) {
            $usuarioRolId = $this->usuarioRolId();

            // Solo puede haber UN contacto principal por alumno: si este lo es, los demás dejan de serlo.
            if ($esPrincipal) {
                TutorAlumno::query()
                    ->where('alumno_id', $alumno->id)
                    ->where('es_contacto_principal', true)
                    ->update(['es_contacto_principal' => false, 'persona_id_updated' => $usuarioRolId, 'updated_at' => now()]);
            }

            TutorAlumno::create([
                'tutor_id' => $tutor->id,
                'alumno_id' => $alumno->id,
                'parentesco_id' => $datos['parentesco_id'],
                'es_contacto_principal' => $esPrincipal,
                'autorizado_recogida' => $request->boolean('autorizado_recogida'),
                'persona_id_created' => $usuarioRolId,
            ]);
        });

        return redirect()->route('tutores.edit', $tutor)->with('success', 'Alumno asociado.');
    }

    // QUITAR la relación (ruta DELETE /tutores/{tutor}/alumnos/{relacion}).
    // Se borra la fila de tutor_alumno; el tutor y el alumno siguen existiendo.
    public function destroy(Tutores $tutor, TutorAlumno $relacion): RedirectResponse
    {
        abort_if((int) $relacion->tutor_id !== (int) $tutor->id, 404);

        $relacion->delete();

        return redirect()->route('tutores.edit', $tutor)->with('success', 'El alumno ya no está asociado a este tutor.');
    }
}
