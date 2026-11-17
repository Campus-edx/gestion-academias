<?php

// Configuración de la caché: datos guardados temporalmente para ir más rápido.
return [
    // Se guarda en archivos.
    'default' => env('CACHE_STORE', 'file'),
    // Lugares posibles donde guardarla.
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],
    ],
    // Prefijo de los nombres para no mezclarlos con otras aplicaciones.
    'prefix' => env('CACHE_PREFIX', 'erp_formacion_cache_'),
];
