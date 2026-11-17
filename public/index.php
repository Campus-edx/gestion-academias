<?php

// Puerta de entrada: todas las peticiones del navegador empiezan en este archivo.
use Illuminate\Http\Request;

// Apunta la hora de inicio (para medir cuánto tarda).
define('LARAVEL_START', microtime(true));

// Si la aplicación está en modo mantenimiento, muestra ese aviso.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Carga las librerías instaladas con Composer (carpeta vendor).
require __DIR__.'/../vendor/autoload.php';

// Arranca Laravel (bootstrap/app.php) y le pasa la petición del navegador.
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
