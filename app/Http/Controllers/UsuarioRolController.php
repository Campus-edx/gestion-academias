<?php

// Dirección del archivo: carpeta app/Http/Controllers.

namespace App\Http\Controllers;

// Modelos que usa.
use App\Models\UsuarioRol;
use App\Models\Usuarios;
// Piezas de Laravel.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Controlador de los ROLES de un usuario (tabla usuario_rol).
// Jira: SCRUM-190 Implementar asignación de roles.
// Se usa desde la ficha del usuario: añadir un rol y cerrarlo (poner su fecha de fin).
// Un rol no se borra: así se sabe quién hizo qué, aunque ya no tenga ese rol
// (persona_id_created de todas las tablas apunta a una fila de usuario_rol).
class UsuarioRolController extends Controlador
{
    // AÑADIR un rol (ruta POST /usuarios/{usuario}/roles).
    public function store(Request $request, Usuarios $usuario): RedirectResponse
    {
        // NUEVO (permisos): dar roles es parte de 'usuarios.editar'.
        $this->authorize('usuarios.editar');

        // Seguridad: solo usuarios de mi empresa.
        abort_if((int) $usuario->empresa_id !== (int) $request->user()->empresa_id, 404);

        // validateWithBag('rol'): los errores van a la tarjeta de roles, no a la de datos.
        $datos = $request->validateWithBag('rol', [
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
            // El centro es opcional, pero si se elige tiene que ser de la empresa del usuario.
            'centro_id' => ['nullable', 'integer',
                Rule::exists('centros', 'id')->where('empresa_id', $usuario->empresa_id)],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ], [
            'rol_id.required' => 'Elige el rol.',
            'centro_id.exists' => 'Ese centro no es de la empresa del usuario.',
            'fecha_inicio.required' => 'Indica desde qué fecha.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ]);

        $centroId = $datos['centro_id'] ?? null;
        $fin = $datos['fecha_fin'] ?? null;

        // Regla que no comprueba la base de datos: el mismo rol, en el mismo centro (o en todos),
        // no puede estar dos veces a la vez. Dos periodos se pisan si uno empieza antes de que
        // acabe el otro y acaba después de que empiece (una fecha de fin vacía = sin final).
        $repetido = UsuarioRol::query()
            ->where('usuario_id', $usuario->id)
            ->where('rol_id', $datos['rol_id'])
            // whereNull si es "todos los centros"; si no, ese centro.
            ->when($centroId, fn ($q) => $q->where('centro_id', $centroId), fn ($q) => $q->whereNull('centro_id'))
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $datos['fecha_inicio']))
            ->when($fin, fn ($q) => $q->where('fecha_inicio', '<=', $fin))
            ->exists();

        if ($repetido) {
            return back()->withInput()->withErrors([
                'rol_id' => 'Ya tiene ese rol (en ese centro) en esas fechas. Ciérralo antes o cambia las fechas.',
            ], 'rol');
        }

        UsuarioRol::create([
            'usuario_id' => $usuario->id,
            'rol_id' => $datos['rol_id'],
            'centro_id' => $centroId,
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $fin,
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('usuarios.edit', $usuario)->with('success', 'Rol añadido.');
    }

    // CERRAR un rol poniendo su fecha de fin (ruta PUT /usuarios/{usuario}/roles/{usuarioRol}/cerrar).
    public function cerrar(Request $request, Usuarios $usuario, UsuarioRol $usuarioRol): RedirectResponse
    {
        $this->authorize('usuarios.editar');

        // Seguridad: el usuario es de mi empresa y el rol es de ESE usuario.
        abort_if((int) $usuario->empresa_id !== (int) $request->user()->empresa_id, 404);
        abort_if((int) $usuarioRol->usuario_id !== (int) $usuario->id, 404);

        $datos = $request->validateWithBag('cerrar', [
            'fecha_fin' => ['required', 'date', 'after_or_equal:'.$usuarioRol->fecha_inicio->toDateString()],
        ], [
            'fecha_fin.required' => 'Indica la fecha de fin.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior al inicio del rol.',
        ]);

        $usuarioRol->update([
            'fecha_fin' => $datos['fecha_fin'],
            'persona_id_updated' => $this->usuarioRolId(),
        ]);

        return redirect()->route('usuarios.edit', $usuario)->with('success', 'Rol cerrado.');
    }
}
