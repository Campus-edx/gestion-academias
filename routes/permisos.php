<?php

use App\Http\Controllers\PermisosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    // RUTAS DE PERMISOS (SCRUM-187)
    Route::get('/permisos', [PermisosController::class, 'index'])->name('permisos.index');
    Route::put('/permisos', [PermisosController::class, 'update'])->name('permisos.update');
});