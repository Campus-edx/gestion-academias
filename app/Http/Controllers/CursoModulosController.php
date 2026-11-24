<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

use App\Models\CursoModulo;
use App\Models\Cursos;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Controlador de los MÓDULOS DE UN CURSO (tabla curso_modulo), desde la ficha del curso:
// añadir un módulo con su orden y sus horas, y quitarlo.
class CursoModulosController extends Controlador
{
    // AÑADIR un módulo al curso (ruta POST /cursos/{curso}/modulos).
    public function store(Request $request, Cursos $curso): RedirectResponse
    {
        // Seguridad: solo cursos de la empresa del usuario.
        abort_if((int) $curso->empresa_id !== (int) $request->user()->empresa_id, 404);

        // Errores en su propio saco ("modulo") para mostrarlos en su tarjeta.
        $datos = $request->validateWithBag('modulo', [
            'modulo_id' => ['required', 'integer', 'exists:modulos,id'],
            'orden' => ['required', 'integer', 'between:1,255'],
            'horas' => ['required', 'integer', 'between:1,9999'],
        ], [
            'modulo_id.required' => 'Elige el módulo.',
            'orden.required' => 'Indica el orden (1, 2, 3...).',
            'horas.required' => 'Indica las horas del módulo.',
            'horas.between' => 'Las horas tienen que ser un número entre 1 y 9999.',
        ]);

        // Un módulo no puede estar dos veces en el mismo curso (la base de datos tampoco lo deja).
        if ($curso->modulosDelCurso()->where('modulo_id', $datos['modulo_id'])->exists()) {
            return back()->withInput()->withErrors(['modulo_id' => 'Ese módulo ya está en el curso.'], 'modulo');
        }

        CursoModulo::create($datos + [
            'curso_id' => $curso->id,
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('cursos.edit', $curso)->with('success', 'Módulo añadido al curso.');
    }

    // QUITAR un módulo del curso (ruta DELETE /cursos/{curso}/modulos/{cursoModulo}).
    public function destroy(Request $request, Cursos $curso, CursoModulo $cursoModulo): RedirectResponse
    {
        // Seguridad: curso de la empresa del usuario, y el módulo tiene que ser de este curso.
        abort_if((int) $curso->empresa_id !== (int) $request->user()->empresa_id, 404);
        abort_if((int) $cursoModulo->curso_id !== (int) $curso->id, 404);

        try {
            $cursoModulo->delete();
        } catch (QueryException $e) {
            // 23000 = otra tabla lo está usando (tarifas, matrículas, sesiones...): la base de datos no deja borrarlo.
            if ($e->getCode() === '23000') {
                return back()->withErrors([
                    'modulo_id' => 'No se puede quitar: ese módulo ya se usa en tarifas, matrículas o sesiones de este curso.',
                ], 'modulo');
            }
            throw $e;
        }

        return redirect()->route('cursos.edit', $curso)->with('success', 'Módulo quitado del curso.');
    }
}
