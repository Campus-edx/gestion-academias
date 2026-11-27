<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        then: function () {
            require __DIR__ . '/../routes/grupos.php';
            require __DIR__ . '/../routes/organizaciones.php';
            require __DIR__ . '/../routes/permisos.php';
            require __DIR__ . '/../routes/temporadas.php';
            require __DIR__ . '/../routes/cursos.php';
            require __DIR__ . '/../routes/tutores.php';
            require __DIR__ . '/../routes/contactos.php';
            require __DIR__ . '/../routes/inscripciones.php';
            require __DIR__ . '/../routes/tarifas.php';
        },
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Los middleware específicos se incorporarán cuando existan módulos que los necesiten.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Punto central para personalizar el tratamiento de excepciones en el futuro.
    })->create();
