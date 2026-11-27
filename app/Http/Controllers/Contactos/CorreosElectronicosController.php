<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contactos;

use App\Http\Controllers\Controlador;
use App\Http\Requests\StoreCorreoRequest;
use App\Http\Requests\UpdateCorreoRequest;
use App\Models\Contactos\EntidadesCorreos;
use App\Services\CentrosService;
use App\Services\CorreosElectronicosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CorreosElectronicosController extends Controlador
{
    public function __construct(
        private readonly CentrosService $centrosService,
        private readonly CorreosElectronicosService $correosService
    ) {}

    public function create(int $centroId): View
    {
        $centro = $this->centrosService->obtenerParaEditar($centroId);

        return view('contactos.centros.correos.create', compact('centro'));
    }

    public function store(
        StoreCorreoRequest $request,
        int $centroId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->correosService->crearParaCentro(
            $centroId,
            $request->validated(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'El correo electrónico se ha añadido correctamente.');
    }

    public function edit(int $centroId, int $relacionId): View
    {
        $centro = $this->centrosService->obtenerParaEditar($centroId);

        $relacion = EntidadesCorreos::query()
            ->with('correoElectronico')
            ->where('centro_id', $centroId)
            ->whereKey($relacionId)
            ->firstOrFail();

        return view('contactos.centros.correos.edit', compact(
            'centro',
            'relacion'
        ));
    }

    public function update(
        UpdateCorreoRequest $request,
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->correosService->actualizarDeCentro(
            $centroId,
            $relacionId,
            $request->validated(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'El correo electrónico se ha actualizado correctamente.');
    }

    public function marcarPrincipal(
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->correosService->marcarPrincipal(
            $centroId,
            $relacionId,
            $this->usuarioRolId()
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'El correo principal se ha actualizado.');
    }

    public function destroy(
        int $centroId,
        int $relacionId
    ): RedirectResponse {
        $this->centrosService->obtenerParaEditar($centroId);

        $this->correosService->desvincularDeCentro(
            $centroId,
            $relacionId
        );

        return redirect()
            ->route('contactos.centros.show', ['id' => $centroId])
            ->with('success', 'El correo electrónico se ha desvinculado correctamente.');
    }
}