<?php

// Dirección del archivo: carpeta app/Http/Controllers.

namespace App\Http\Controllers;

// Modelos que usa esta pantalla.
use App\Models\Roles;
use App\Models\Usuarios;
// Piezas de Laravel.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de USUARIOS (Jira: SCRUM-181 Gestionar usuarios, subtarea SCRUM-185 Modificación).
// Cada usuario es de UNA empresa: aquí solo se ven y se crean los de la empresa del usuario conectado.
// Los roles de cada usuario se añaden y se cierran en su ficha (UsuarioRolController).
// No hay borrado: un usuario se da de baja desmarcando "Activo" (ya no puede entrar).
class UsuariosController extends Controlador
{
    // LISTADO (ruta GET /usuarios), con buscador por nombre o correo.
    public function index(Request $request): View
    {
        // NUEVO (permisos): solo quien tiene 'usuarios.ver'. Si no, página 403.
        $this->authorize('usuarios.ver');

        // trim() quita los espacios de los lados; (string) por si no llega nada.
        $buscar = trim((string) $request->query('buscar', ''));

        $usuarios = Usuarios::query()
            // Solo los de mi empresa.
            ->where('empresa_id', $request->user()->empresa_id)
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(fn ($donde) => $donde
                    ->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('correo_electronico', 'like', "%{$buscar}%"));
            })
            // with(...): trae de una vez los roles vigentes de todos (y el nombre de cada rol),
            // en vez de hacer una consulta por cada fila de la tabla.
            ->with(['roles' => fn ($q) => $q->vigentes()->with('rol')])
            ->orderBy('nombre')
            ->get();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'buscar' => $buscar,
        ]);
    }

    // FORMULARIO DE ALTA (ruta GET /usuarios/crear).
    public function create(): View
    {
        $this->authorize('usuarios.editar');

        // new Usuarios([...]): un usuario vacío, para que el formulario sirva para alta y edición.
        return view('usuarios.formulario', $this->datosFormulario(new Usuarios(['activo' => true])));
    }

    // GUARDAR UN USUARIO NUEVO (ruta POST /usuarios).
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('usuarios.editar');

        $datos = $this->validar($request);

        $usuario = Usuarios::create([
            // La empresa no se elige: es la del usuario conectado.
            'empresa_id' => $request->user()->empresa_id,
            'nombre' => $datos['nombre'],
            'correo_electronico' => $datos['correo_electronico'],
            // No hace falta cifrarla: el modelo la cifra solo ('contrasena' => 'hashed' en casts).
            'contrasena' => $datos['contrasena'],
            'activo' => $request->boolean('activo'),
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        // Se va a su ficha, porque lo siguiente es darle un rol.
        return redirect()->route('usuarios.edit', $usuario)
            ->with('success', 'Usuario creado. Ahora dale al menos un rol: sin rol no podrá trabajar.');
    }

    // FICHA DEL USUARIO (ruta GET /usuarios/{usuario}/editar).
    public function edit(Request $request, Usuarios $usuario): View
    {
        // Ver la ficha basta con 'usuarios.ver'. Los botones de guardar los esconde la vista
        // con @can('usuarios.editar'), y update() lo vuelve a comprobar.
        $this->authorize('usuarios.ver');
        $this->comprobarEmpresa($request, $usuario);

        return view('usuarios.formulario', $this->datosFormulario($usuario));
    }

    // GUARDAR CAMBIOS (ruta PUT /usuarios/{usuario}).
    public function update(Request $request, Usuarios $usuario): RedirectResponse
    {
        $this->authorize('usuarios.editar');
        $this->comprobarEmpresa($request, $usuario);

        $datos = $this->validar($request, $usuario);

        // Nadie puede desactivarse a sí mismo: se quedaría fuera de la aplicación.
        if ($usuario->id === $request->user()->id && ! $request->boolean('activo')) {
            return back()->withInput()->withErrors(['activo' => 'No puedes desactivar tu propio usuario.']);
        }

        $cambios = [
            'nombre' => $datos['nombre'],
            'correo_electronico' => $datos['correo_electronico'],
            'activo' => $request->boolean('activo'),
            'persona_id_updated' => $this->usuarioRolId(),
        ];

        // La contraseña solo se cambia si se ha escrito una nueva. Vacía = se queda la que tenía.
        if (! empty($datos['contrasena'])) {
            $cambios['contrasena'] = $datos['contrasena'];
        }

        $usuario->update($cambios);

        return redirect()->route('usuarios.edit', $usuario)->with('success', 'Usuario actualizado.');
    }

    // ─── Métodos privados: solo los usa este controlador ───

    // Seguridad: un usuario de otra empresa no se puede ver ni modificar ("No encontrado").
    private function comprobarEmpresa(Request $request, Usuarios $usuario): void
    {
        abort_if((int) $usuario->empresa_id !== (int) $request->user()->empresa_id, 404);
    }

    // Reglas del formulario. $usuario llega al editar (null al crear).
    private function validar(Request $request, ?Usuarios $usuario = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            // unique: no puede haber dos usuarios con el mismo correo.
            // ->ignore(...): al editar, el suyo propio no cuenta como repetido.
            'correo_electronico' => ['required', 'email', 'max:160',
                Rule::unique('usuarios', 'correo_electronico')->ignore($usuario?->id)],
            // Al crear es obligatoria; al editar, opcional (solo si se quiere cambiar).
            // confirmed: tiene que coincidir con el campo "contrasena_confirmation" (repetir contraseña).
            'contrasena' => [$usuario ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ], [
            'nombre.required' => 'Escribe el nombre.',
            'correo_electronico.required' => 'Escribe el correo electrónico.',
            'correo_electronico.email' => 'El correo electrónico no es válido.',
            'correo_electronico.unique' => 'Ya hay un usuario con ese correo electrónico.',
            'contrasena.required' => 'Escribe una contraseña.',
            'contrasena.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las dos contraseñas no coinciden.',
        ]);
    }

    // Todo lo que necesita la vista del formulario (alta y ficha).
    private function datosFormulario(Usuarios $usuario): array
    {
        return [
            'usuario' => $usuario,
            // Para la tarjeta de roles (solo en la ficha): todos sus roles, el más reciente primero.
            'rolesDelUsuario' => $usuario->exists
                ? $usuario->roles()->with('rol')->orderByDesc('fecha_inicio')->get() : collect(),
            // Desplegables para añadir un rol.
            'roles' => Roles::query()->orderBy('nombre')->get(),
            // Los centros son de la Persona 3: se leen con una consulta sencilla, sin su modelo.
            // Solo los de la empresa del usuario.
            'centros' => $usuario->exists
                ? DB::table('centros')->where('empresa_id', $usuario->empresa_id)->orderBy('nombre')->get(['id', 'nombre'])
                : collect(),
        ];
    }
}
