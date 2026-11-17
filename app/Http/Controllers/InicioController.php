<?php

// Dirección del archivo: carpeta app/Http/Controllers.
namespace App\Http\Controllers;

// Piezas que usa: la petición del navegador y el tipo de respuesta "vista".
use Illuminate\Http\Request;
use Illuminate\View\View;

// Controlador del panel de inicio (dashboard). Hereda de Controlador.
class InicioController extends Controlador
{
    // __invoke: su único método. Se ejecuta solo al llamar al controlador desde la ruta /dashboard.
    public function __invoke(Request $request): View
    {
        // Devuelve la vista resources/views/inicio/index.blade.php con estos datos:
        return view('inicio.index', [
            // El usuario que ha iniciado sesión.
            'usuario' => $request->user(),
            // La empresa de ese usuario (relación empresa() del modelo Usuarios).
            'empresa' => $request->user()->empresa,
        ]);
    }
}
