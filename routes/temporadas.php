<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Temporadas\TemporadasController;

Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/temporadas', [TemporadasController::class, 'index'])
        ->name('temporadas.index');

    Route::get('/temporadas/crear', [TemporadasController::class, 'create'])
        ->name('temporadas.create');

    Route::post('/temporadas', [TemporadasController::class, 'store'])
        ->name('temporadas.store');

    Route::get('/temporadas/{id}', [TemporadasController::class, 'show'])
        ->name('temporadas.show');

    Route::get('/temporadas/{id}/editar', [TemporadasController::class, 'edit'])
        ->name('temporadas.edit');

    Route::put('/temporadas/{id}', [TemporadasController::class, 'update'])
        ->name('temporadas.update');

    Route::delete('/temporadas/{id}', [TemporadasController::class, 'destroy'])
        ->name('temporadas.destroy');
});
