<?php

// Configuración de las vistas.
return [
    // Carpeta donde están las vistas Blade.
    'paths' => [
        resource_path('views'),
    ],
    // Carpeta donde Laravel guarda las vistas ya convertidas a PHP.
    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views')) ?: storage_path('framework/views')
    ),
];
