<?php

// Configuración del cifrado de contraseñas.
return [
    // Método de cifrado: bcrypt.
    'driver' => env('HASH_DRIVER', 'bcrypt'),
    'bcrypt' => [
        // Cuántas vueltas de cifrado: más = más seguro pero más lento.
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => env('HASH_VERIFY', true),
        'limit' => env('BCRYPT_LIMIT', null),
    ],
    // Otro método de cifrado, no usado aquí.
    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => env('HASH_VERIFY', true),
    ],
    // Si cambia la configuración, vuelve a cifrar la contraseña al hacer login.
    'rehash_on_login' => true,
];
