<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

use App\Models\CursoEquipamiento;
use App\Models\Cursos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Controlador del EQUIPAMIENTO QUE NECESITA UN CURSO (tabla curso_equipamiento), desde su ficha:
// "12 ordenadores", "1 proyector". Lo usa la Persona 3 para buscar un aula que sirva.
class CursoEquipamientoController extends Controlador
{
    // AÑADIR (ruta POST /cursos/{curso}/equipamiento).
    public function store(Request $request, Cursos $curso): RedirectResponse
    {
        // Seguridad: solo cursos de la empresa del usuario.
        abort_if((int) $curso->empresa_id !== (int) $request->user()->empresa_id, 404);

        $datos = $request->validateWithBag('equipamiento', [
            'equipamiento_id' => ['required', 'integer', 'exists:equipamientos,id'],
            'cantidad' => ['required', 'integer', 'between:1,1000'],
        ], [
            'equipamiento_id.required' => 'Elige el equipamiento.',
            'cantidad.required' => 'Indica cuántas unidades hacen falta.',
        ]);

        // El mismo equipamiento solo una vez por curso (si hacen falta más, se cambia la cantidad).
        $existe = CursoEquipamiento::query()
            ->where('curso_id', $curso->id)
            ->where('equipamiento_id', $datos['equipamiento_id'])
            ->exists();
        if ($existe) {
            return back()->withInput()->withErrors([
                'equipamiento_id' => 'Ese equipamiento ya está en la lista. Quítalo y vuelve a añadirlo con otra cantidad.',
            ], 'equipamiento');
        }

        CursoEquipamiento::create($datos + [
            'curso_id' => $curso->id,
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('cursos.edit', $curso)->with('success', 'Equipamiento añadido.');
    }

    // QUITAR (ruta DELETE /cursos/{curso}/equipamiento/{cursoEquipamiento}).
    public function destroy(Request $request, Cursos $curso, CursoEquipamiento $cursoEquipamiento): RedirectResponse
    {
        abort_if((int) $curso->empresa_id !== (int) $request->user()->empresa_id, 404);
        abort_if((int) $cursoEquipamiento->curso_id !== (int) $curso->id, 404);

        $cursoEquipamiento->delete();

        return redirect()->route('cursos.edit', $curso)->with('success', 'Equipamiento quitado.');
    }
}
