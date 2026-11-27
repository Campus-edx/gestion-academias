<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Grupos\GruposController;

Route::middleware(['web', 'auth'])->group(function () {

    // Mostrar formulario de creación.
    Route::get('/grupos/crear', [GruposController::class, 'create'])
        ->name('grupos.create');

    // Cargar cursos, centros y plataformas según la temporada.
    Route::get('/grupos/opciones/{temporadaId}', [GruposController::class, 'opciones'])
        ->name('grupos.opciones');

    // Guardar grupo nuevo.
    Route::post('/grupos', [GruposController::class, 'store'])
        ->name('grupos.store');

    // Mostrar listado de grupos.
    Route::get('/grupos', [GruposController::class, 'index'])
        ->name('grupos.index');

    // Mostrar ficha de un grupo.
    Route::get('/grupos/{id}', [GruposController::class, 'show'])
        ->whereNumber('id')
        ->name('grupos.show');

    // Mostrar el formulario de edición de un grupo.
    Route::get('/grupos/{id}/editar', [GruposController::class, 'edit'])
        ->name('grupos.edit');

    // Guardar los cambios de un grupo.
    Route::put('/grupos/{id}', [GruposController::class, 'update'])
        ->name('grupos.update');
});
