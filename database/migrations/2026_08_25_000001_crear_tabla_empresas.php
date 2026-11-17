<?php

// Piezas para crear tablas desde código.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Una migración: instrucciones para crear (up) o deshacer (down) tablas. Se ejecuta con php artisan migrate.
return new class extends Migration {
    // up(): lo que se hace al migrar.
    public function up(): void
    {
        // Crea la tabla empresas con estas columnas:
        Schema::create('empresas', function (Blueprint $table) {
            // id: número único que se pone solo (clave primaria).
            $table->id();
            // string = texto; el número es la longitud máxima.
            $table->string('razon_social', 180);
            // nullable = puede quedarse vacío.
            $table->string('nombre_comercial', 180)->nullable();
            $table->string('cif', 30)->nullable();
            $table->string('correo_electronico', 160)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('direccion', 220)->nullable();
            $table->string('codigo_postal', 15)->nullable();
            $table->string('localidad', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            // default = valor que se pone si no se indica otro.
            $table->string('pais', 100)->default('España');
            $table->string('nombre_aplicacion', 80)->default('ERP Formación');
            $table->string('color_principal', 20)->default('#0d6efd');
            $table->string('ruta_logo')->nullable();
            // boolean = sí/no.
            $table->boolean('activo')->default(true);
            // Crea created_at y updated_at (fechas de creación y modificación).
            $table->timestamps();
            // Campos de auditoría: quién creó y quién modificó el registro.
            $table->unsignedBigInteger('persona_id_created')->nullable();
            $table->unsignedBigInteger('persona_id_updated')->nullable();
        });
    }

    // down(): deshace la migración borrando la tabla.
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
