<?php

// Herramienta para crear comandos de consola.
use Illuminate\Support\Facades\Artisan;

// Crea el comando php artisan erp:version.
Artisan::command('erp:version', function () {
    // Escribe este texto en la terminal.
    $this->info('ERP Formación Base - beta 0.1.0');
// Descripción que sale en php artisan list.
})->purpose('Muestra la versión funcional de la beta');
