<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers\Tarifas;

// Modelos que usa esta pantalla.
use App\Models\Tarifas\Descuentos;
use App\Http\Controllers\Controlador;
// Piezas de Laravel.
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Controlador de DESCUENTOS de la empresa (sin historia en Jira: hay que crearla).
// Cada empresa tiene los suyos. No se borran: se desactivan (ya no se ofrecen al matricular).
// Permisos: ver con 'facturas.ver'; crear y modificar con 'facturas.emitir'.
class DescuentosController extends Controlador
{
    // LISTADO (ruta GET /descuentos).
    public function index(Request $request): View
    {
        $this->authorize('facturas.ver');

        return view('descuentos.index', [
            'descuentos' => Descuentos::query()
                ->where('empresa_id', $request->user()->empresa_id)
                ->orderByDesc('activo')   // Primero los activos.
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    // FORMULARIO DE ALTA (ruta GET /descuentos/crear).
    public function create(): View
    {
        $this->authorize('facturas.emitir');

        return view('descuentos.formulario', ['descuento' => new Descuentos(['activo' => true, 'tipo' => 'porcentaje'])]);
    }

    // GUARDAR UNO NUEVO (ruta POST /descuentos).
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('facturas.emitir');

        Descuentos::create($this->validar($request) + [
            'empresa_id' => $request->user()->empresa_id,
            'activo' => $request->boolean('activo'),
            'persona_id_created' => $this->usuarioRolId(),
        ]);

        return redirect()->route('descuentos.index')->with('success', 'Descuento creado.');
    }

    // FICHA (ruta GET /descuentos/{descuento}/editar).
    public function edit(Request $request, Descuentos $descuento): View
    {
        $this->authorize('facturas.emitir');
        // Seguridad: solo los de mi empresa.
        abort_if((int) $descuento->empresa_id !== (int) $request->user()->empresa_id, 404);

        return view('descuentos.formulario', ['descuento' => $descuento]);
    }

    // GUARDAR CAMBIOS (ruta PUT /descuentos/{descuento}).
    public function update(Request $request, Descuentos $descuento): RedirectResponse
    {
        $this->authorize('facturas.emitir');
        abort_if((int) $descuento->empresa_id !== (int) $request->user()->empresa_id, 404);

        $descuento->update($this->validar($request) + [
            'activo' => $request->boolean('activo'),
            'persona_id_updated' => $this->usuarioRolId(),
        ]);

        return redirect()->route('descuentos.index')->with('success', 'Descuento actualizado.');
    }

    // Reglas del formulario.
    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'tipo' => ['required', 'in:porcentaje,importe'],
            // gt:0 = mayor que cero. Rule::when: si es porcentaje, además no puede pasar de 100.
            'valor' => ['required', 'numeric', 'gt:0', 'max:999999.99',
                Rule::when($request->input('tipo') === 'porcentaje', ['max:100'])],
        ], [
            'nombre.required' => 'Escribe el nombre del descuento.',
            'tipo.required' => 'Elige si es un porcentaje o un importe.',
            'valor.required' => 'Escribe el valor.',
            'valor.numeric' => 'El valor tiene que ser un número (por ejemplo, 10 o 12.50).',
            'valor.gt' => 'El valor tiene que ser mayor que cero.',
            'valor.max' => 'El valor es demasiado alto (un porcentaje no puede pasar de 100).',
        ]);
    }
}
