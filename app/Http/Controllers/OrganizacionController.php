<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Modelos que usa esta pantalla.
use App\Models\Organizaciones;
// Piezas de Laravel.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de la ORGANIZACIÓN CENTRAL (Jira: SCRUM-170, subtareas 172 alta y 173 modificación).
// Una sola pantalla: si todavía no hay organización, sale vacía y al guardar se crea;
// si ya hay, se modifica. No hay listado porque solo hay una.
// Permiso: el mismo que para gestionar empresas ('empresas.editar').
class OrganizacionController extends Controlador
{
    // VER (ruta GET /organizacion).
    public function edit(): View
    {
        $this->authorize('empresas.editar');

        return view('organizacion.formulario', [
            // La primera que haya, o una vacía (new) si todavía no existe ninguna.
            'organizacion' => Organizaciones::query()->orderBy('id')->first() ?? new Organizaciones(),
        ]);
    }

    // GUARDAR (ruta PUT /organizacion): crea la organización o modifica la que hay.
    public function update(Request $request): RedirectResponse
    {
        $this->authorize('empresas.editar');

        $organizacion = Organizaciones::query()->orderBy('id')->first();

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:180'],
            // El CIF no se puede repetir (la base de datos también lo impide).
            // ->ignore(...): la propia organización no cuenta como repetida.
            'cif' => ['nullable', 'string', 'max:30', Rule::unique('organizaciones', 'cif')->ignore($organizacion?->id)],
            'correo_electronico' => ['nullable', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:40'],
        ], [
            'nombre.required' => 'Escribe el nombre de la organización.',
            'cif.unique' => 'Ya hay otra organización con ese CIF.',
            'correo_electronico.email' => 'El correo electrónico no es válido.',
        ]);

        if ($organizacion) {
            $organizacion->update($datos + ['persona_id_updated' => $this->usuarioRolId()]);
            $mensaje = 'Datos de la organización actualizados.';
        } else {
            Organizaciones::create($datos + ['persona_id_created' => $this->usuarioRolId()]);
            $mensaje = 'Organización registrada.';
        }

        return redirect()->route('organizacion.edit')->with('success', $mensaje);
    }
}