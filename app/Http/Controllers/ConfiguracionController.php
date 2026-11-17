<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Piezas que usa: respuestas, petición, cifrado de contraseñas, reglas de validación y vistas.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de la pantalla Configuración: datos del usuario y cambio de contraseña.
class ConfiguracionController extends Controlador
{
    // MOSTRAR la pantalla de configuración (ruta GET /configuracion).
    public function editar(Request $request): View
    {
        // Devuelve la vista configuracion/index.blade.php con el usuario conectado.
        return view('configuracion.index', [
            'usuario' => $request->user(),
        ]);
    }

    // GUARDAR nombre y correo (ruta PUT /configuracion).
    public function actualizar(Request $request): RedirectResponse
    {
        // El usuario que ha iniciado sesión.
        $usuario = $request->user();

        // Comprueba los datos del formulario. Si algo falla, vuelve al formulario con los errores.
        $datos = $request->validate([
            // Nombre: obligatorio, texto, máximo 120 caracteres.
            'nombre' => ['required', 'string', 'max:120'],
            // Correo: obligatorio, con formato de email, máximo 160 caracteres...
            'correo_electronico' => [
                'required',
                'email',
                'max:160',
                // ...y que no lo tenga ya otro usuario (sin contar al propio usuario).
                Rule::unique('usuarios', 'correo_electronico')->ignore($usuario->id),
            ],
        ], [
            // Mensajes de error en castellano.
            'nombre.required' => 'Introduce tu nombre.',
            'correo_electronico.required' => 'Introduce tu email.',
            'correo_electronico.email' => 'Introduce un email válido.',
            'correo_electronico.unique' => 'Ese email ya está siendo utilizado por otro usuario.',
        ]);

        // Guarda los cambios en la tabla usuarios.
        $usuario->update($datos);

        // Vuelve a la pantalla con un mensaje verde de éxito.
        return back()->with('success', 'Tus datos se han actualizado correctamente.');
    }

    // CAMBIAR la contraseña (ruta PUT /configuracion/password).
    public function actualizarContrasena(Request $request): RedirectResponse
    {
        // Comprueba el formulario de contraseña.
        $datos = $request->validate([
            // Contraseña actual: obligatoria.
            'contrasena_actual' => ['required', 'string'],
            // Nueva: obligatoria, mínimo 8 caracteres y repetida igual (confirmed = campo contrasena_confirmation).
            'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            // Mensajes de error en castellano.
            'contrasena_actual.required' => 'Introduce tu contraseña actual.',
            'contrasena.required' => 'Introduce la nueva contraseña.',
            'contrasena.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        // El usuario que ha iniciado sesión.
        $usuario = $request->user();

        // Si la contraseña actual escrita NO coincide con la guardada (cifrada)...
        if (!Hash::check($datos['contrasena_actual'], $usuario->contrasena)) {
            // ...vuelve al formulario con este error y no cambia nada.
            return back()->withErrors([
                'contrasena_actual' => 'La contraseña actual no es correcta.',
            ]);
        }

        // Guarda la nueva contraseña. Se cifra sola gracias al cast 'hashed' del modelo.
        $usuario->update([
            'contrasena' => $datos['contrasena'],
        ]);

        // Vuelve a la pantalla con mensaje de éxito.
        return back()->with('success', 'Tu contraseña se ha cambiado correctamente.');
    }
}
