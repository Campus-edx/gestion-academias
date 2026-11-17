<?php

use Illuminate\Support\Str;

// Configuración de la base de datos.
return [
    // Conexión que se usa: mysql.
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        // Datos de la conexión MySQL. Los valores reales vienen del .env.
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            // Servidor: 127.0.0.1 = tu propio ordenador.
            'host' => env('DB_HOST', '127.0.0.1'),
            // Puerto de MySQL (3306 en XAMPP).
            'port' => env('DB_PORT', '3306'),
            // Nombre de la base de datos.
            'database' => env('DB_DATABASE', 'erp_formacion_beta'),
            // Usuario y contraseña de MySQL (root sin contraseña en XAMPP).
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            // Codificación: permite tildes, ñ y emojis.
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            // Modo estricto: MySQL avisa de datos incorrectos en vez de arreglarlos solo.
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::ATTR_EMULATE_PREPARES => true,
            ]) : [],
        ],
    ],

    // Tabla donde Laravel apunta qué migraciones ya se han ejecutado (en castellano: migraciones).
    'migrations' => [
        'table' => 'migraciones',
        'update_date_on_publish' => true,
    ],

    // Redis: otro tipo de almacén. No se usa en este proyecto.
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],
];
