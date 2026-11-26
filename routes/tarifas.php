<?php

use App\Http\Controllers\Tarifas\DescuentosController;
use App\Http\Controllers\Tarifas\TarifasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    // RUTAS DE PERMISOS (SCRUM-187)
    // ─────────────────────────────────────────────────────────
    // RUTAS DE TARIFAS Y DESCUENTOS (SCRUM-354)
    // Las tarifas no se modifican ni se borran: se cierran (fecha "hasta").
    // Los descuentos no se borran: se desactivan.
    // ─────────────────────────────────────────────────────────
    Route::get('/tarifas', [TarifasController::class, 'index'])->name('tarifas.index');
    Route::get('/tarifas/crear', [TarifasController::class, 'create'])->name('tarifas.create');
    Route::post('/tarifas', [TarifasController::class, 'store'])->name('tarifas.store');
    Route::put('/tarifas/{tarifa}/cerrar', [TarifasController::class, 'cerrar'])->name('tarifas.cerrar');

    Route::get('/descuentos', [DescuentosController::class, 'index'])->name('descuentos.index');
    Route::get('/descuentos/crear', [DescuentosController::class, 'create'])->name('descuentos.create');
    Route::post('/descuentos', [DescuentosController::class, 'store'])->name('descuentos.store');
    Route::get('/descuentos/{descuento}/editar', [DescuentosController::class, 'edit'])->name('descuentos.edit');
    Route::put('/descuentos/{descuento}', [DescuentosController::class, 'update'])->name('descuentos.update');

});