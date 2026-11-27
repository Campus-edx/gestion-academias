<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CentrosController;
use App\Http\Controllers\Contactos\DireccionesController;
use App\Http\Controllers\Contactos\TelefonosController;
use App\Http\Controllers\Contactos\CorreosElectronicosController;

Route::middleware(['web', 'auth'])->prefix('contactos')->name('contactos.')->group(function () {

    // Centros
    Route::get('/centros', [CentrosController::class, 'index'])
        ->name('centros.index');

    Route::get('/centros/crear', [CentrosController::class, 'create'])
        ->name('centros.create');

    Route::post('/centros', [CentrosController::class, 'store'])
        ->name('centros.store');

    Route::get('/centros/{id}/editar', [CentrosController::class, 'edit'])
        ->name('centros.edit');

    Route::put('/centros/{id}', [CentrosController::class, 'update'])
        ->name('centros.update');

    // Direcciones de centros
    Route::post('/centros/{centroId}/direcciones', [DireccionesController::class, 'store'])
        ->name('centros.direcciones.store');

    Route::get('/centros/{centroId}/direcciones/crear', [DireccionesController::class, 'create'])
        ->name('centros.direcciones.create');

    Route::get('/centros/{centroId}/direcciones/{relacionId}/editar', [DireccionesController::class, 'edit'])
        ->name('centros.direcciones.edit');

    Route::put('/centros/{centroId}/direcciones/{relacionId}', [DireccionesController::class, 'update'])
        ->name('centros.direcciones.update');

    Route::post('/centros/{centroId}/direcciones/{relacionId}/principal', [DireccionesController::class, 'marcarPrincipal'])
        ->name('centros.direcciones.principal');

    Route::delete('/centros/{centroId}/direcciones/{relacionId}', [DireccionesController::class, 'destroy'])
        ->name('centros.direcciones.destroy');

    // Teléfonos de centros
    Route::post('/centros/{centroId}/telefonos', [TelefonosController::class, 'store'])
        ->name('centros.telefonos.store');

    Route::get('/centros/{centroId}/telefonos/crear', [TelefonosController::class, 'create'])
        ->name('centros.telefonos.create');

    Route::get('/centros/{centroId}/telefonos/{relacionId}/editar', [TelefonosController::class, 'edit'])
        ->name('centros.telefonos.edit');

    Route::put('/centros/{centroId}/telefonos/{relacionId}', [TelefonosController::class, 'update'])
        ->name('centros.telefonos.update');

    Route::post('/centros/{centroId}/telefonos/{relacionId}/principal', [TelefonosController::class, 'marcarPrincipal'])
        ->name('centros.telefonos.principal');

    Route::delete('/centros/{centroId}/telefonos/{relacionId}', [TelefonosController::class, 'destroy'])
        ->name('centros.telefonos.destroy');

    // Correos electrónicos de centros
    Route::post('/centros/{centroId}/correos', [CorreosElectronicosController::class, 'store'])
        ->name('centros.correos.store');

    Route::get('/centros/{centroId}/correos/crear', [CorreosElectronicosController::class, 'create'])
        ->name('centros.correos.create');

    Route::get('/centros/{centroId}/correos/{relacionId}/editar', [CorreosElectronicosController::class, 'edit'])
        ->name('centros.correos.edit');

    Route::put('/centros/{centroId}/correos/{relacionId}', [CorreosElectronicosController::class, 'update'])
        ->name('centros.correos.update');

    Route::post('/centros/{centroId}/correos/{relacionId}/principal', [CorreosElectronicosController::class, 'marcarPrincipal'])
        ->name('centros.correos.principal');

    Route::delete('/centros/{centroId}/correos/{relacionId}', [CorreosElectronicosController::class, 'destroy'])
        ->name('centros.correos.destroy');

    // Ficha del centro
    Route::get('/centros/{id}', [CentrosController::class, 'show'])
        ->name('centros.show');

    // Listado de grupos de un centro
    Route::get('/centros/{id}/grupos', [CentrosController::class, 'grupos'])
        ->name('centros.grupos');
});
