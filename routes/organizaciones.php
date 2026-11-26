<?php

// use App\Http\Controllers\OrganizacionesController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\OrganizacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/organizaciones', [OrganizacionController::class, 'index'])->name('organizaciones.index');
    // ... resto de rutas del módulo

     // ─────────────────────────────────────────────────────────
    // RUTAS DE ORGANIZACIÓN Y EMPRESAS (SCRUM-170 y 175)
    // No hay ruta para borrar empresas: se dan de baja desmarcando "Activa".
    // ─────────────────────────────────────────────────────────

    // La organización: una sola pantalla para verla y guardarla.
    Route::get('/organizacion', [OrganizacionController::class, 'edit'])->name('organizacion.edit');
    Route::put('/organizacion', [OrganizacionController::class, 'update'])->name('organizacion.update');

    // Empresas: listado, alta, ficha y cambios.
    Route::get('/empresas', [EmpresasController::class, 'index'])->name('empresas.index');
    Route::get('/empresas/crear', [EmpresasController::class, 'create'])->name('empresas.create');
    Route::post('/empresas', [EmpresasController::class, 'store'])->name('empresas.store');
    Route::get('/empresas/{empresa}/editar', [EmpresasController::class, 'edit'])->name('empresas.edit');
    Route::put('/empresas/{empresa}', [EmpresasController::class, 'update'])->name('empresas.update');
    
});