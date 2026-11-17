<?php

// Dirección del archivo: carpeta database/seeders.
namespace Database\Seeders;

use App\Models\Empresas;
use App\Models\Usuarios;
use Illuminate\Database\Seeder;

// Seeder: mete datos de prueba en la base de datos. Se ejecuta con php artisan db:seed.
class DatabaseSeeder extends Seeder
{
    // run(): lo que hace al ejecutarse.
    public function run(): void
    {
        // Crea una empresa de ejemplo y la guarda en $empresa.
        $empresa = Empresas::create([
            'razon_social' => 'Academia Horizonte Demo S.L.',
            'nombre_comercial' => 'Horizonte Formación',
            'cif' => 'B12345678',
            'correo_electronico' => 'info@horizontedemo.local',
            'telefono' => '910 000 000',
            'direccion' => 'Calle Formación, 12',
            'codigo_postal' => '28001',
            'localidad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'España',
            'nombre_aplicacion' => 'ERP Formación',
            'color_principal' => '#0d6efd',
            'activo' => true,
        ]);

        // Crea el usuario administrador de prueba, asociado a esa empresa.
        Usuarios::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Administrador Demo',
            'correo_electronico' => 'admin@demo.local',
            // Se escribe en claro aquí, pero se guarda cifrada (cast 'hashed').
            'contrasena' => 'Admin1234!',
            'activo' => true,
        ]);

        // Crea un segundo usuario de prueba.
        Usuarios::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Usuario Demo',
            'correo_electronico' => 'usuario@demo.local',
            'contrasena' => 'Usuario1234!',
            'activo' => true,
        ]);
    }
}
