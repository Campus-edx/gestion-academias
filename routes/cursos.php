<?php

use Illuminate\Support\Facades\Route;

// ── TROZO 1: los seis "use" del manual ──
use App\Http\Controllers\CategoriasCursoController;
use App\Http\Controllers\CursoEquipamientoController;
use App\Http\Controllers\CursoModulosController;
use App\Http\Controllers\CursosController;
use App\Http\Controllers\MateriasController;
use App\Http\Controllers\ModulosController;

Route::middleware(['web', 'auth'])->group(function () {

  // ─────────────────────────────────────────────────────────
    // OFERTA FORMATIVA (SCRUM-255): categorías, materias, módulos y cursos.
    // Ninguno se borra: se desactivan con la casilla "Activo".
    // ─────────────────────────────────────────────────────────

    // Categorías de curso (catálogo). En la dirección van con guion: /categorias-curso.
    Route::get('/categorias-curso', [CategoriasCursoController::class, 'index'])->name('categorias_curso.index');
    Route::get('/categorias-curso/crear', [CategoriasCursoController::class, 'create'])->name('categorias_curso.create');
    Route::post('/categorias-curso', [CategoriasCursoController::class, 'store'])->name('categorias_curso.store');
    Route::get('/categorias-curso/{categoria}/editar', [CategoriasCursoController::class, 'edit'])->name('categorias_curso.edit');
    Route::put('/categorias-curso/{categoria}', [CategoriasCursoController::class, 'update'])->name('categorias_curso.update');

    // Materias (catálogo).
    Route::get('/materias', [MateriasController::class, 'index'])->name('materias.index');
    Route::get('/materias/crear', [MateriasController::class, 'create'])->name('materias.create');
    Route::post('/materias', [MateriasController::class, 'store'])->name('materias.store');
    Route::get('/materias/{materia}/editar', [MateriasController::class, 'edit'])->name('materias.edit');
    Route::put('/materias/{materia}', [MateriasController::class, 'update'])->name('materias.update');

    // Módulos.
    Route::get('/modulos', [ModulosController::class, 'index'])->name('modulos.index');
    Route::get('/modulos/crear', [ModulosController::class, 'create'])->name('modulos.create');
    Route::post('/modulos', [ModulosController::class, 'store'])->name('modulos.store');
    Route::get('/modulos/{modulo}/editar', [ModulosController::class, 'edit'])->name('modulos.edit');
    Route::put('/modulos/{modulo}', [ModulosController::class, 'update'])->name('modulos.update');

    // Cursos.
    Route::get('/cursos', [CursosController::class, 'index'])->name('cursos.index');
    Route::get('/cursos/crear', [CursosController::class, 'create'])->name('cursos.create');
    Route::post('/cursos', [CursosController::class, 'store'])->name('cursos.store');
    Route::get('/cursos/{curso}/editar', [CursosController::class, 'edit'])->name('cursos.edit');
    Route::put('/cursos/{curso}', [CursosController::class, 'update'])->name('cursos.update');

    // Módulos de un curso (con orden y horas): añadir y quitar.
    Route::post('/cursos/{curso}/modulos', [CursoModulosController::class, 'store'])->name('cursos.modulos.store');
    Route::delete('/cursos/{curso}/modulos/{cursoModulo}', [CursoModulosController::class, 'destroy'])->name('cursos.modulos.destroy');

    // Equipamiento que necesita el aula de un curso: añadir y quitar.
    Route::post('/cursos/{curso}/equipamiento', [CursoEquipamientoController::class, 'store'])->name('cursos.equipamiento.store');
    Route::delete('/cursos/{curso}/equipamiento/{cursoEquipamiento}', [CursoEquipamientoController::class, 'destroy'])->name('cursos.equipamiento.destroy');

});
