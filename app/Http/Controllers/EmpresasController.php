<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Modelos que usa esta pantalla.
use App\Models\Empresas;
use App\Models\Organizaciones;
// Piezas de Laravel.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de EMPRESAS (Jira: SCRUM-175, subtareas 177 alta, 178 modificación y 179 baja lógica).
// A diferencia de usuarios, aquí se ven TODAS las empresas: gestionar empresas es tarea del
// administrador general, que trabaja por encima de ellas (permisos empresas.ver y empresas.editar).
// No hay borrado: una empresa se da de baja desmarcando "Activa" y sus usuarios ya no pueden entrar.
class EmpresasController extends Controlador
{
    // LISTADO (ruta GET /empresas), con buscador y filtro de activas.
    public function index(Request $request): View
    {
        $this->authorize('empresas.ver');

        $buscar = trim((string) $request->query('buscar', ''));

        $empresas = Empresas::query()
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(fn ($donde) => $donde
                    ->where('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('cif', 'like', "%{$buscar}%"));
            })
            ->with('organizacion')
            // withCount: añade a cada empresa el número de usuarios ($empresa->usuarios_count)
            // sin traerlos todos.
            ->withCount('usuarios')
            ->orderBy('razon_social')
            ->get();

        return view('empresas.index', [
            'empresas' => $empresas,
            'buscar' => $buscar,
        ]);
    }

    // FORMULARIO DE ALTA (ruta GET /empresas/crear).
    public function create(): View
    {
        $this->authorize('empresas.editar');

        // Valores por defecto de una empresa nueva: activa, el nombre y el color de la base de datos.
        return view('empresas.formulario', $this->datosFormulario(new Empresas([
            'activo' => true,
            'nombre_aplicacion' => 'ERP Formación',
            'color_principal' => '#0d6efd',
        ])));
    }

    // GUARDAR UNA EMPRESA NUEVA (ruta POST /empresas).
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('empresas.editar');

        $datos = $this->validar($request);

        $empresa = Empresas::create($datos + [
            'activo' => $request->boolean('activo'),
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('empresas.edit', $empresa)
            ->with('success', 'Empresa creada. Para que alguien pueda trabajar en ella, crea sus usuarios desde Usuarios.');
    }

    // FICHA (ruta GET /empresas/{empresa}/editar).
    public function edit(Empresas $empresa): View
    {
        $this->authorize('empresas.ver');

        return view('empresas.formulario', $this->datosFormulario($empresa));
    }

    // GUARDAR CAMBIOS (ruta PUT /empresas/{empresa}).
    public function update(Request $request, Empresas $empresa): RedirectResponse
    {
        $this->authorize('empresas.editar');

        $datos = $this->validar($request, $empresa);

        // Nadie puede desactivar SU PROPIA empresa: se quedaría fuera de la aplicación
        // (el login no deja entrar a usuarios de empresas desactivadas).
        // (int): los dos como número, para comparar seguro con ===.
        if ((int) $empresa->id === (int) $request->user()->empresa_id && ! $request->boolean('activo')) {
            return back()->withInput()->withErrors(['activo' => 'No puedes desactivar tu propia empresa: te quedarías fuera.']);
        }

        $empresa->update($datos + [
            'activo' => $request->boolean('activo'),
            'persona_id_updated' => $this->usuarioRolId(),
        ]);

        return redirect()->route('empresas.edit', $empresa)->with('success', 'Empresa actualizada.');
    }

    // ─── Métodos privados ───

    // Reglas del formulario. $empresa llega al editar (null al crear).
    private function validar(Request $request, ?Empresas $empresa = null): array
    {
        $datos = $request->validate([
            'razon_social' => ['required', 'string', 'max:180'],
            'nombre_comercial' => ['nullable', 'string', 'max:180'],
            // El CIF identifica a la empresa: no se repite. (La base de datos no lo impide;
            // lo comprueba el controlador.)
            'cif' => ['nullable', 'string', 'max:30', Rule::unique('empresas', 'cif')->ignore($empresa?->id)],
            'correo_electronico' => ['nullable', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'organizacion_id' => ['nullable', 'integer', 'exists:organizaciones,id'],
            // Rule::requiredIf: obligatorio SOLO si se ha elegido organización.
            // La base de datos exige lo mismo (CHECK empresas_vinculacion_check).
            'tipo_vinculacion' => [Rule::requiredIf($request->filled('organizacion_id')), 'nullable', 'in:propietaria,colaboradora'],
            'nombre_aplicacion' => ['required', 'string', 'max:80'],
            // regex: un color como #0d6efd (almohadilla y 6 caracteres de 0-9 o a-f).
            'color_principal' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'razon_social.required' => 'Escribe la razón social.',
            'cif.unique' => 'Ya hay otra empresa con ese CIF.',
            'correo_electronico.email' => 'El correo electrónico no es válido.',
            'tipo_vinculacion.required' => 'Si la empresa es de una organización, indica si es propietaria o colaboradora.',
            'nombre_aplicacion.required' => 'Escribe el nombre que verá la aplicación.',
            'color_principal.regex' => 'El color tiene que ser como #0d6efd.',
        ]);

        // Sin organización no hay tipo de vinculación (si no, la base de datos lo rechazaría).
        if (empty($datos['organizacion_id'])) {
            $datos['organizacion_id'] = null;
            $datos['tipo_vinculacion'] = null;
        }

        return $datos;
    }

    // Todo lo que necesita la vista del formulario.
    private function datosFormulario(Empresas $empresa): array
    {
        return [
            'empresa' => $empresa,
            'organizaciones' => Organizaciones::query()->orderBy('nombre')->get(),
        ];
    }
}