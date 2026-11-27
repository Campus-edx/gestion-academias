<?php

namespace App\Http\Controllers\Grupos;

use App\Http\Controllers\Controlador;
use App\Services\GruposService;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class GruposController extends Controlador
{
    protected GruposService $gruposService;

    /**
     * Inyectar el servicio de grupos.
     */
    public function __construct(GruposService $gruposService)
    {
        $this->gruposService = $gruposService;
    }

    //.................................. index ....................................................
    public function index()
    {
        $grupos = $this->gruposService->listar();

        $puedeCrear = $this->gruposService->puedeCrear();

        $gruposEditables = [];

        foreach ($grupos as $grupo) {
            $gruposEditables[$grupo->id] =
                $this->gruposService->puedeEditar((int) $grupo->id);
        }

        return view('grupos.index', compact(
            'grupos',
            'puedeCrear',
            'gruposEditables'
        ));
    }


    //.................................. show ....................................................
    public function show($id)
    {
        $grupo = $this->gruposService->obtener((int) $id);

        $puedeEditar = $this->gruposService->puedeEditar(
            (int) $grupo->id
        );

        return view('grupos.show', compact(
            'grupo',
            'puedeEditar'
        ));
    }


    //.................................. create ....................................................
    public function create()
    {
        $this->gruposService->autorizarCreacion();

        $temporadas = $this->gruposService->temporadasDisponibles();

        return view('grupos.formulario', [
            'temporadas' => $temporadas,
            'grupo' => null,
            'modoEdicion' => false,
        ]);
    }

    /**
     * Obtener cursos, centros y plataformas de una temporada.
     */
    public function opciones($temporadaId)
    {
        $opciones = $this->gruposService->opciones(
            (int) $temporadaId
        );

        return response()->json([
            'cursos' => $opciones['cursos'],
            'centros' => $opciones['centros'],
            'plataformas' => $opciones['plataformas'],
        ]);
    }

    /**
     * Guardar un grupo nuevo.
     */
    public function store(Request $request)
    {
        $this->gruposService->autorizarCreacion();

        $this->gruposService->crear(
            $request->all(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('grupos.index')
            ->with('success', 'El grupo se ha creado correctamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit($id)
    {
        $grupo = $this->gruposService->obtenerParaEditar(
            (int) $id
        );

        $temporadas = $this->gruposService->temporadasDisponibles(
            (int) $grupo->temporada_id
        );

        return view('grupos.formulario', [
            'temporadas' => $temporadas,
            'grupo' => $grupo,
            'modoEdicion' => true,
        ]);
    }

    /**
     * Actualizar un grupo existente.
     */
    public function update(Request $request, $id)
    {
        $this->gruposService->actualizar(
            (int) $id,
            $request->all(),
            $this->usuarioRolId()
        );

        return redirect()
            ->route('grupos.index')
            ->with('success', 'El grupo se ha actualizado correctamente.');
    }
}
