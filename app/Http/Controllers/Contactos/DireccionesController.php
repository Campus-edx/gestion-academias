<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contactos;

use App\Http\Controllers\Controlador;
use App\Http\Requests\StoreDireccionRequest;
use App\Http\Requests\UpdateDireccionRequest;
use App\Models\Contactos\EntidadesDirecciones;
use App\Models\Geografias\Municipios;
use App\Services\CentrosService;
use App\Services\DireccionesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DireccionesController extends Controlador
{
    public function __construct(
        private readonly CentrosService $centrosService,
        private readonly DireccionesService $direccionesService
    ) {}

    /**
     * Mostrar el formulario para añadir una dirección.
     */
    public function create(int $centroId): View
    {
        $centro = $this->centrosService->obtenerParaEditar($centroId);

        $municipios = Municipios::query()
            ->with('provincia')
            ->orderBy('nombre')
            ->get();

        return view('contactos.centros.direcciones.create', compact(
            'centro',
            'municipios'
        ));
    }

    /**
     * Guardar una dirección y asociarla al centro.
     */
    public function store(
        StoreDireccionRequest $request,
        int $centroId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->direccionesService->crearParaCentro(
            $centroId,
            $request->validated(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'La dirección se ha añadido correctamente.');
    }

    /**
     * Mostrar el formulario para editar una dirección.
     */
    public function edit(int $centroId, int $relacionId): View
    {
        $centro = $this->centrosService->obtenerParaEditar($centroId);

        $relacion = EntidadesDirecciones::query()
            ->with('direccion')
            ->where('centro_id', $centroId)
            ->whereKey($relacionId)
            ->firstOrFail();

        $municipios = Municipios::query()
            ->with('provincia')
            ->orderBy('nombre')
            ->get();

        return view('contactos.centros.direcciones.edit', compact(
            'centro',
            'relacion',
            'municipios'
        ));
    }

    /**
     * Actualizar una dirección asociada al centro.
     */
    public function update(
        UpdateDireccionRequest $request,
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->direccionesService->actualizarDeCentro(
            $centroId,
            $relacionId,
            $request->validated(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'La dirección se ha actualizado correctamente.');
    }

    /**
     * Marcar una dirección como principal.
     */
    public function marcarPrincipal(
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->direccionesService->marcarPrincipal(
            $centroId,
            $relacionId,
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'La dirección principal se ha actualizado.');
    }

    /**
     * Desvincular una dirección del centro.
     */
    public function destroy(
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->direccionesService->desvincularDeCentro(
            $centroId,
            $relacionId
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'La dirección se ha desvinculado correctamente.');
    }
}
