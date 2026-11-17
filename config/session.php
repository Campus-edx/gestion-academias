<?php

use Illuminate\Support\Str;

// Configuración de las sesiones (cómo se recuerda quién ha iniciado sesión).
return [
    // Las sesiones se guardan en archivos (storage/framework/sessions).
    'driver' => env('SESSION_DRIVER', 'file'),
    // Minutos de inactividad antes de cerrar la sesión (120 = 2 horas).
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    // false = la sesión no se cierra al cerrar el navegador.
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
    // Si se cifran los datos de la sesión.
    'encrypt' => env('SESSION_ENCRYPT', false),
    // Carpeta donde se guardan.
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => env('SESSION_TABLE', 'sessions'),
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],
    // Nombre de la cookie que guarda el navegador.
    'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'erp-formacion'), '_').'_session'),
    'path' => env('SESSION_PATH', '/'),
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE'),
    // La cookie no se puede leer con JavaScript (seguridad).
    'http_only' => env('SESSION_HTTP_ONLY', true),
    // Protección para que otras webs no usen la cookie.
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),
];
