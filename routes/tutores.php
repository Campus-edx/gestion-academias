<?php

use Illuminate\Support\Facades\Route;

// Tutores y su relación con los alumnos.
use App\Http\Controllers\TutorAlumnoController;
use App\Http\Controllers\TutoresController;

Route::middleware(['web', 'auth'])->group(function () {

    // ─────────────────────────────────────────────────────────
    // RUTAS DE TUTORES (SCRUM-210 y 215)
    // No hay ruta para borrar tutores. La relación con un alumno sí se puede quitar.
    // ─────────────────────────────────────────────────────────
    Route::get('/tutores', [TutoresController::class, 'index'])->name('tutores.index');
    Route::get('/tutores/crear', [TutoresController::class, 'create'])->name('tutores.create');
    Route::post('/tutores', [TutoresController::class, 'store'])->name('tutores.store');
    Route::get('/tutores/{tutor}/editar', [TutoresController::class, 'edit'])->name('tutores.edit');
    Route::put('/tutores/{tutor}', [TutoresController::class, 'update'])->name('tutores.update');

    // Alumnos a cargo del tutor: asociar uno y quitar la relación.
    Route::post('/tutores/{tutor}/alumnos', [TutorAlumnoController::class, 'store'])->name('tutores.alumnos.store');
    Route::delete('/tutores/{tutor}/alumnos/{relacion}', [TutorAlumnoController::class, 'destroy'])->name('tutores.alumnos.destroy');
});