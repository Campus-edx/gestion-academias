<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Modelos que usa.
use App\Models\ProfesorEmpresa;
use App\Models\Profesores;
// Piezas de Laravel.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Controlador de las VINCULACIONES de un profesor con las empresas (tabla profesor_empresa).
// Jira: SCRUM-237 Asociar profesores a empresas y SCRUM-242 Gestionar tipos de vinculación.
// Se usa desde la ficha del profesor: añadir una vinculación y cerrarla.
// Para CAMBIAR de tipo (autónomo → empleado) o de precio: se cierra la actual y se añade otra.
// Así queda el historial de cómo ha trabajado con cada empresa.
class VinculacionesProfesoresController extends Controlador
{
    // AÑADIR una vinculación (ruta POST /profesores/{profesor}/vinculaciones).
    public function store(Request $request, Profesores $profesor): RedirectResponse
    {
        // validateWithBag('vinculacion'): los errores se guardan en un "saco" con ese nombre,
        // para mostrarlos en la tarjeta de vinculaciones y no mezclarlos con los de otros formularios.
        $datos = $request->validateWithBag('vinculacion', [
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'tipo_vinculacion' => ['required', 'in:empleado,autonomo'],
            // numeric = número (admite decimales con punto: 18.50). min:0 = no negativo.
            'precio_hora' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'irpf_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fecha_inicio' => ['required', 'date'],
        ], [
            'empresa_id.required' => 'Elige la empresa.',
            'tipo_vinculacion.required' => 'Elige si es empleado o autónomo.',
            'precio_hora.numeric' => 'El precio por hora tiene que ser un número (por ejemplo, 18.50).',
            'irpf_porcentaje.max' => 'El IRPF no puede pasar de 100.',
            'fecha_inicio.required' => 'Indica desde qué fecha.',
        ]);

        // Regla que no comprueba la base de datos: con la misma empresa, solo una vinculación a la vez.
        // Choca si hay otra con esa empresa que siga abierta en la fecha de inicio nueva
        // (sin fecha de fin, o con una fecha de fin igual o posterior).
        $choca = ProfesorEmpresa::query()
            ->where('profesor_id', $profesor->id)
            ->where('empresa_id', $datos['empresa_id'])
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $datos['fecha_inicio']))
            ->exists();

        if ($choca) {
            return back()->withInput()->withErrors([
                'empresa_id' => 'Ya tiene una vinculación abierta con esa empresa en esa fecha. '
                    . 'Ciérrala primero (para cambiar de tipo o de precio se cierra la actual y se crea otra).',
            ], 'vinculacion');
        }

        ProfesorEmpresa::create($datos + [
            'profesor_id' => $profesor->id,
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('profesores.edit', $profesor)->with('success', 'Vinculación añadida.');
    }

    // CERRAR una vinculación poniendo su fecha de fin
    // (ruta PUT /profesores/{profesor}/vinculaciones/{vinculacion}/cerrar).
    public function cerrar(Request $request, Profesores $profesor, ProfesorEmpresa $vinculacion): RedirectResponse
    {
        // Seguridad: la vinculación tiene que ser de ESTE profesor (si no, "no encontrada").
        abort_if((int) $vinculacion->profesor_id !== (int) $profesor->id, 404);

        $datos = $request->validateWithBag('cerrar', [
            // after_or_equal: la fecha de fin no puede ser anterior a la de inicio.
            'fecha_fin' => ['required', 'date', 'after_or_equal:' . $vinculacion->fecha_inicio->toDateString()],
        ], [
            'fecha_fin.required' => 'Indica la fecha de fin.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior al inicio de la vinculación.',
        ]);

        $vinculacion->update([
            'fecha_fin' => $datos['fecha_fin'],
            'persona_id_updated' => $this->usuarioRolId(),
        ]);

        return redirect()->route('profesores.edit', $profesor)->with('success', 'Vinculación cerrada.');
    }
}
