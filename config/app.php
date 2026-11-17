<?php

// Configuración general de la aplicación. env('X', valor) = usa X del archivo .env o, si no existe, el valor indicado.
return [
    // Nombre de la aplicación.
    'name' => env('APP_NAME', 'ERP Formación'),
    // Entorno: local (desarrollo) o production (real).
    'env' => env('APP_ENV', 'production'),
    // true = muestra los errores con detalle. Nunca en producción.
    'debug' => (bool) env('APP_DEBUG', false),
    // Dirección de la aplicación.
    'url' => env('APP_URL', 'http://localhost'),
    // Zona horaria.
    'timezone' => 'Europe/Madrid',
    // Idioma.
    'locale' => env('APP_LOCALE', 'es'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'es'),
    // Idioma de los datos de prueba inventados.
    'faker_locale' => env('APP_FAKER_LOCALE', 'es_ES'),
    // Tipo de cifrado y clave (APP_KEY, la que crea php artisan key:generate).
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => array_filter(explode(',', env('APP_PREVIOUS_KEYS', ''))),
    // Cómo se guarda el modo mantenimiento.
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
