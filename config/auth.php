<?php

// Este archivo es config/auth.php: la CONFIGURACIÓN DE LA AUTENTICACIÓN.
// "Autenticación" = comprobar quién es alguien (el login).
// Aquí no hay acciones: solo se le dan a Laravel unos ajustes, como cuando
// eliges opciones en el menú de configuración de un programa.
//
// "return [ ... ];" significa: "este archivo devuelve esta lista de ajustes".
// Los corchetes [ ] forman una lista, y cada ajuste se escribe así:
//     'nombre_del_ajuste' => valor
// Una lista puede tener otras listas dentro, como carpetas dentro de carpetas.

return [

    // ─────────────────────────────────────────────────────────
    // 'defaults' = los AJUSTES POR DEFECTO.
    // Lo que Laravel usará siempre, salvo que en algún sitio se le pida otra cosa.
    // ─────────────────────────────────────────────────────────
    'defaults' => [

        // Qué "guardia" (sistema de vigilancia del login) se usa por defecto.
        // env('AUTH_GUARD', 'web') significa:
        // "mira si en el archivo .env hay un ajuste llamado AUTH_GUARD;
        //  si lo hay, usa su valor; si no lo hay, usa 'web'".
        // Como en nuestro .env no existe, se usa la guardia 'web' (definida más abajo).
        'guard' => env('AUTH_GUARD', 'web'),

        // Qué sistema de "recuperar contraseña olvidada" se usa por defecto.
        // Igual que arriba: si .env no dice otra cosa, se usa 'usuarios'
        // (definido al final, en el bloque 'passwords').
        'passwords' => env('AUTH_PASSWORD_BROKER', 'usuarios'),
    ],


    // ─────────────────────────────────────────────────────────
    // 'guards' = las GUARDIAS.
    // Una guardia decide CÓMO se recuerda que alguien ha iniciado sesión
    // mientras va pasando de una página a otra.
    // ─────────────────────────────────────────────────────────
    'guards' => [

        // Aquí se define la guardia llamada 'web', la que usamos por defecto.
        'web' => [

            // 'driver' = el mecanismo que usa.
            // 'session' significa que recuerda al usuario mediante una SESIÓN:
            // al iniciar sesión, el navegador recibe una especie de pulsera de
            // identificación (una cookie) y la enseña en cada página que visita.
            'driver' => 'session',

            // 'provider' = de dónde saca los datos de los usuarios.
            // Apunta al proveedor llamado 'usuarios', definido en el bloque siguiente.
            'provider' => 'usuarios',
        ],
    ],


    // ─────────────────────────────────────────────────────────
    // 'providers' = los PROVEEDORES DE USUARIOS.
    // Le dicen a Laravel dónde están guardados los usuarios y cómo buscarlos.
    // ─────────────────────────────────────────────────────────
    'providers' => [

        // El proveedor llamado 'usuarios' (el que usa la guardia 'web').
        'usuarios' => [

            // 'eloquent' significa que los usuarios se buscan a través de un MODELO
            // (Eloquent es el sistema de Laravel para trabajar con la base de datos).
            'driver' => 'eloquent',

            // Y este es el modelo concreto: App\Models\Usuarios, que trabaja con
            // la tabla "usuarios". MODELO DE AUTENTICACION :
            // ESTA es la línea más importante del archivo: Laravel, por defecto,
            // buscaría un modelo llamado "User" con la tabla "users" (en inglés).
            // Aquí se le dice que use nuestro modelo en castellano.
            // Gracias a esto, auth()->user() devuelve un objeto de Usuarios.
            // "::class" escribe el nombre completo del modelo para que Laravel lo encuentre.
            'model' => App\Models\Usuarios::class,
        ],
    ],


    // ─────────────────────────────────────────────────────────
    // 'passwords' = la RECUPERACIÓN DE CONTRASEÑAS OLVIDADAS
    // (lo típico de "¿Has olvidado tu contraseña? Te enviamos un código").
    // Ojo: la aplicación todavía NO tiene esa pantalla ni sus rutas.
    // Esto solo deja preparada la configuración por si se añade en el futuro.
    // ─────────────────────────────────────────────────────────
    'passwords' => [

        // El sistema de recuperación llamado 'usuarios' (el de por defecto).
        'usuarios' => [

            // A qué usuarios se aplica: los del proveedor 'usuarios' (los de arriba).
            'provider' => 'usuarios',

            // En qué tabla se guardan los códigos de recuperación que se envían.
            // Si .env no dice otra cosa, se usa la tabla
            // 'codigos_restablecimiento_contrasena' (está creada en la base de datos).
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'codigos_restablecimiento_contrasena'),

            // 'expire' = caducidad: el código deja de valer a los 60 MINUTOS.
            'expire' => 60,

            // 'throttle' = freno: hay que esperar 60 SEGUNDOS antes de pedir
            // otro código. Evita que alguien pida cientos de códigos seguidos.
            'throttle' => 60,
        ],
    ],


    // 'password_timeout' = cuánto tiempo, en SEGUNDOS, se da por buena una
    // confirmación de contraseña. Algunas aplicaciones vuelven a pedirte la
    // contraseña antes de hacer algo delicado (por ejemplo, borrar la cuenta);
    // después de confirmarla, no te la vuelven a pedir durante este tiempo.
    // 10800 segundos = 3 horas. Igual que antes: si .env no dice otra cosa.
    // Ahora mismo el ERP no usa esta función.
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

]; // Aquí se cierra la lista de ajustes. El punto y coma ; termina la instrucción.
