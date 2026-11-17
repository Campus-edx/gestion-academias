<?php

// Configuración de dónde se guardan los archivos subidos.
return [
    // Disco por defecto: local.
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        // Archivos privados: storage/app/private.
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],
        // Archivos públicos (visibles por navegador): storage/app/public.
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],
    ],
    // Atajo de public/storage a storage/app/public (php artisan storage:link).
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
