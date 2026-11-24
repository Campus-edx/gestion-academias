<?php

namespace App\Http\Controllers\Temporadas;

use App\Http\Controllers\Controlador;
use App\Models\Empresas;
use App\Models\Temporadas\Temporadas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemporadasController extends Controlador
{

    //..................... Mostrar el listado de temporadas. ...........................................
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $temporadas = Temporadas::with('empresa')
            ->when($buscar, function ($consulta, $buscar) {
                $consulta->where(function ($subconsulta) use ($buscar) {
                    $subconsulta->where('nombre', 'like', "%{$buscar}%")
                        ->orWhereHas('empresa', function ($empresa) use ($buscar) {
                            $empresa->where('razon_social', 'like', "%{$buscar}%")
                                ->orWhere('nombre_comercial', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderBy('fecha_inicio', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('temporadas.index', compact('temporadas', 'buscar'));
    }


    //..................... Mostrar el formulario para crear una temporada. ...........................................
    public function create()
    {
        // Obtener únicamente las empresas activas.
        $empresas = Empresas::where('activo', 1)
            ->orderBy('nombre_comercial')
            ->get();

        // Crear una temporada vacía para el formulario.
        $temporada = new Temporadas();

        return view('temporadas.formulario', compact(
            'temporada',
            'empresas'
        ));
    }


    //..................... Guardar una nueva temporada. ...........................................
    public function store(Request $request)
    {
        // Validar los datos recibidos.
        $datos = $request->validate([
            'empresa_id' => [
                'required',
                Rule::exists('empresas', 'id')->where('activo', 1),
            ],
            'nombre' => [
                'required',
                'string',
                'max:80',
                Rule::unique('temporadas', 'nombre')
                    ->where('empresa_id', $request->empresa_id),
            ],
            'fecha_inicio' => [
                'required',
                'date',
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],
            'estado' => [
                'required',
                Rule::in(['planificada', 'activa', 'cerrada']),
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        // Obtener el usuario y rol que realiza la operación.
        $usuarioRolId = $this->usuarioRolId();

        // Registrar los datos de auditoría.
        $datos['persona_id_created'] = $usuarioRolId;
        $datos['persona_id_updated'] = $usuarioRolId;

        // Crear la temporada.
        Temporadas::create($datos);

        return redirect()
            ->route('temporadas.index')
            ->with('success', 'La temporada se ha creado correctamente.');
    }


    //..................... Mostrar el formulario para editar una temporada. ...........................................
    public function edit($id)
    {
        $temporada = Temporadas::findOrFail($id);

        // Obtener las empresas activas.
        $empresas = Empresas::where('activo', 1)
            ->orderBy('nombre_comercial')
            ->get();

        // Incluir la empresa actual aunque haya sido desactivada.
        if (!$empresas->contains('id', $temporada->empresa_id)) {
            $empresaActual = Empresas::find($temporada->empresa_id);

            if ($empresaActual) {
                $empresas->push($empresaActual);
            }
        }

        return view('temporadas.formulario', compact(
            'temporada',
            'empresas'
        ));
    }


    //..................... Actualizar una temporada existente. ........................................................
    public function update(Request $request, $id)
    {
        $temporada = Temporadas::findOrFail($id);

        // Validar los datos recibidos.
        $datos = $request->validate([
            'empresa_id' => [
                'required',
                'exists:empresas,id',
            ],
            'nombre' => [
                'required',
                'string',
                'max:80',
                Rule::unique('temporadas', 'nombre')
                    ->where('empresa_id', $request->empresa_id)
                    ->ignore($temporada->id),
            ],
            'fecha_inicio' => [
                'required',
                'date',
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],
            'estado' => [
                'required',
                Rule::in(['planificada', 'activa', 'cerrada']),
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        // Si se cambia de empresa, la nueva debe estar activa.
        if (
            $datos['empresa_id'] != $temporada->empresa_id
            && !Empresas::where('id', $datos['empresa_id'])
                ->where('activo', 1)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'empresa_id' => 'La empresa seleccionada no está activa.',
                ])
                ->withInput();
        }

        // Registrar quién ha realizado la modificación.
        $datos['persona_id_updated'] = $this->usuarioRolId();

        // Actualizar la temporada.
        $temporada->update($datos);

        return redirect()
            ->route('temporadas.index')
            ->with('success', 'La temporada se ha actualizado correctamente.');
    }


    //..................... Mostrar la ficha de una temporada. ...............................................................
    public function show($id)
    {
        $temporada = Temporadas::with(['empresa', 'grupos'])
            ->findOrFail($id);

        return view('temporadas.show', compact('temporada'));
    }


    //..................... Eliminar una temporada si no tiene grupos asociados. ..................................................

    public function destroy($id)
    {
        $temporada = Temporadas::findOrFail($id);

        // Comprobar si existen grupos asociados.
        if ($temporada->grupos()->exists()) {
            return redirect()
                ->route('temporadas.index')
                ->with('error', 'No se puede eliminar la temporada porque tiene grupos asociados.');
        }

        $temporada->delete();

        return redirect()
            ->route('temporadas.index')
            ->with('success', 'La temporada se ha eliminado correctamente.');
    }
}
