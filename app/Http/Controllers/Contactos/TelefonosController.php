<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contactos;

use App\Http\Controllers\Controlador;
use App\Http\Requests\StoreTelefonoRequest;
use App\Http\Requests\UpdateTelefonoRequest;
use App\Models\Contactos\EntidadesTelefonos;
use App\Services\CentrosService;
use App\Services\TelefonosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TelefonosController extends Controlador
{
    public function __construct(
        private readonly CentrosService $centrosService,
        private readonly TelefonosService $telefonosService
    ) {}

    public function create(int $centroId): View
    {
        $centro = $this->centrosService->obtenerParaEditar($centroId);

        return view('contactos.centros.telefonos.create', compact('centro'));
    }

    public function store(
        StoreTelefonoRequest $request,
        int $centroId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->telefonosService->crearParaCentro(
            $centroId,
            $request->validated(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'El teléfono se ha añadido correctamente.');
    }

    public function edit(int $centroId, int $relacionId): View
    {
        $centro = $this->centrosService->obtenerParaEditar($centroId);

        $relacion = EntidadesTelefonos::query()
            ->with('telefono')
            ->where('centro_id', $centroId)
            ->whereKey($relacionId)
            ->firstOrFail();

        return view('contactos.centros.telefonos.edit', compact(
            'centro',
            'relacion'
        ));
    }

    public function update(
        UpdateTelefonoRequest $request,
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->telefonosService->actualizarDeCentro(
            $centroId,
            $relacionId,
            $request->validated(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'El teléfono se ha actualizado correctamente.');
    }

    public function marcarPrincipal(
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->telefonosService->marcarPrincipal(
            $centroId,
            $relacionId,
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'El teléfono principal se ha actualizado.');
    }

    public function destroy(
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->telefonosService->desvincularDeCentro(
            $centroId,
            $relacionId
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'El teléfono se ha desvinculado correctamente.');
    }
}