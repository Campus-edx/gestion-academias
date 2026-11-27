<?php

namespace App\Http\Controllers;

use App\Models\Centros;
use App\Models\Empresas;
use App\Services\CentrosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CentrosController extends Controlador
{
    public function __construct(
        private readonly CentrosService $centrosService,
        private readonly \App\Services\GruposService $gruposService
    ) {}


    //.................................. index ....................................................
    public function index(): View
    {
        return view('centros.index', [
            'centros' => $this->centrosService->listar(),
            'puedeCrear' => $this->centrosService->puedeCrear(),
        ]);
    }

    //.................................. create ....................................................
    public function create(): View
    {
        $this->centrosService->autorizarCreacion();

        return view('centros.formulario', [
            'centro' => new Centros(['activo' => true]),
            'empresas' => $this->centrosService->empresasDisponibles(),
        ]);
    }


    //.................................. store ....................................................
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $this->centrosService->crear(
            $datos,
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.index')
            ->with('success', 'Centro creado correctamente.');
    }


    //.................................. show ....................................................
    public function show(int $id): View
    {
        return view('centros.show', [
            'centro' => $this->centrosService->obtener($id),
        ]);
    }

    //.................................. grupos ....................................................
    public function grupos(int $id): View
    {
        $centro = $this->centrosService->obtener($id);

        $grupos = $this->gruposService->listarPorCentro($id);

        return view('centros.grupos', compact('centro', 'grupos'));
    }

    //.................................. edit ....................................................
    public function edit(int $id): View
    {
        return view('centros.formulario', [
            'centro' => $this->centrosService->obtenerParaEditar($id),
            'empresas' => Empresas::query()
                ->orderBy('razon_social')
                ->get(),
        ]);
    }


    //.................................. update ....................................................
    public function update(Request $request, int $id): RedirectResponse
    {
        $datos = $this->validar($request);

        $this->centrosService->actualizar(
            $id,
            $datos,
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.index')
            ->with('success', 'Centro actualizado correctamente.');
    }


    //.................................. validar ....................................................
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'empresa_id' => [
                'required',
                'integer',
                'exists:empresas,id',
            ],
            'nombre' => [
                'required',
                'string',
            ],
            'activo' => [
                'nullable',
                'boolean',
            ],
        ]);

        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
