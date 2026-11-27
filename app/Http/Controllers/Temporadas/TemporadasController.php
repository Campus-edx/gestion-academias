<?php

namespace App\Http\Controllers\Temporadas;

use App\Http\Controllers\Controlador;
use App\Models\Temporadas\Temporadas;
use App\Services\TemporadasService;
use Illuminate\Http\Request;

class TemporadasController extends Controlador
{
    /**
     * Mostrar el listado de temporadas autorizadas.
     */
    public function index(Request $request, TemporadasService $temporadasService)
    {
        $buscar = $request->input('buscar');

        $temporadas = $temporadasService->listar($buscar);

        $puedeCrear = $temporadasService->puedeCrear();

        $temporadasEditables = $temporadas->getCollection()
            ->mapWithKeys(function ($temporada) use ($temporadasService) {
                return [
                    $temporada->id => $temporadasService->puedeEditar(
                        (int) $temporada->id
                    ),
                ];
            })
            ->all();

        return view('temporadas.index', compact(
            'temporadas',
            'buscar',
            'puedeCrear',
            'temporadasEditables'
        ));
    }

    /**
     * Mostrar el formulario para crear una temporada.
     */
    public function create(TemporadasService $temporadasService)
    {
        $temporadasService->autorizarCreacion();

        $empresas = $temporadasService->empresasDisponibles();

        $temporada = new Temporadas();

        return view('temporadas.formulario', compact(
            'temporada',
            'empresas'
        ));
    }

    /**
     * Guardar una nueva temporada.
     */
    public function store(
        Request $request,
        TemporadasService $temporadasService
    ) {
        $temporadasService->crear(
            $request->all(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('temporadas.index')
            ->with('success', 'La temporada se ha creado correctamente.');
    }

    /**
     * Mostrar el formulario para editar una temporada.
     */
    public function edit($id, TemporadasService $temporadasService)
    {
        $temporada = $temporadasService->obtenerParaEditar((int) $id);

        $empresas = $temporadasService->empresasDisponibles(
            (int) $temporada->empresa_id
        );

        return view('temporadas.formulario', compact(
            'temporada',
            'empresas'
        ));
    }

    /**
     * Actualizar una temporada existente.
     */
    public function update(
        Request $request,
        $id,
        TemporadasService $temporadasService
    ) {
        $temporadasService->actualizar(
            (int) $id,
            $request->all(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('temporadas.index')
            ->with('success', 'La temporada se ha actualizado correctamente.');
    }

    /**
     * Mostrar la ficha de una temporada.
     */
    public function show($id, TemporadasService $temporadasService)
    {
        $temporada = $temporadasService->obtener((int) $id);

        $temporada->load('grupos');

        $puedeEditar = $temporadasService->puedeEditar(
            (int) $temporada->id
        );

        return view('temporadas.show', compact(
            'temporada',
            'puedeEditar'
        ));
    }

    /**
     * Eliminar una temporada si no tiene grupos asociados.
     */
    public function destroy($id, TemporadasService $temporadasService)
    {
        $temporadasService->eliminar((int) $id);

        return redirect()
            ->route('temporadas.index')
            ->with('success', 'La temporada se ha eliminado correctamente.');
    }
}