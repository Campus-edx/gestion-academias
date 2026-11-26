<?php

use Illuminate\Support\Facades\Route;

// Inscripciones (matrículas de alumnos en grupos).
use App\Http\Controllers\Inscripciones\InscripcionesController;

Route::middleware(['web', 'auth'])->group(function () {

    // ─────────────────────────────────────────────────────────
    // RUTAS DE INSCRIPCIONES (SCRUM-273, 278, 282 y 220)
    // No hay ruta para borrar: una inscripción se da de baja (fecha de baja y estado "baja")
    // o, si se hizo por error, se anula (estado "anulada"). Nunca se borra.
    // ─────────────────────────────────────────────────────────
    Route::get('/inscripciones', [InscripcionesController::class, 'index'])->name('inscripciones.index');
    Route::get('/inscripciones/crear', [InscripcionesController::class, 'create'])->name('inscripciones.create');
    Route::post('/inscripciones', [InscripcionesController::class, 'store'])->name('inscripciones.store');
    Route::put('/inscripciones/{inscripcion}/baja', [InscripcionesController::class, 'baja'])->name('inscripciones.baja');
    // El alumno se fue y VUELVE al mismo grupo: se crea una inscripción nueva (la baja queda en el historial).
    Route::post('/inscripciones/{inscripcion}/reactivar', [InscripcionesController::class, 'reactivar'])->name('inscripciones.reactivar');
    // La baja fue un ERROR: la misma inscripción vuelve a estar activa, como si no se hubiera dado de baja.
    Route::put('/inscripciones/{inscripcion}/deshacer-baja', [InscripcionesController::class, 'deshacerBaja'])->name('inscripciones.deshacer-baja');
    // La matrícula fue un ERROR (grupo, pagador o fechas mal): queda anulada y no cuenta para nada.
    Route::put('/inscripciones/{inscripcion}/anular', [InscripcionesController::class, 'anular'])->name('inscripciones.anular');
});
