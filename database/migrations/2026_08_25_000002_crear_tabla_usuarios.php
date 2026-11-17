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
        // Crea la tabla usuarios:
        Schema::create('usuarios', function (Blueprint $table) {
            // id: número único automático (clave primaria).
            $table->id();
            // Clave foránea: cada usuario pertenece a una empresa. Si se borra la empresa, se borran sus usuarios (cascade).
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre', 120);
            // unique = no puede haber dos usuarios con el mismo correo.
            $table->string('correo_electronico', 160)->unique();
            $table->timestamp('fecha_verificacion_correo')->nullable();
            // La contraseña se guarda cifrada.
            $table->string('contrasena');
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_ultimo_acceso')->nullable();
            // Código para la opción "Mantener la sesión iniciada".
            $table->string('token_recuerdo', 100)->nullable();
            // created_at y updated_at.
            $table->timestamps();
            // Campos de auditoría: quién creó y quién modificó.
            $table->unsignedBigInteger('persona_id_created')->nullable();
            $table->unsignedBigInteger('persona_id_updated')->nullable();
        });

        // Tabla para los códigos de "he olvidado mi contraseña" (aún sin usar en la aplicación).
        Schema::create('codigos_restablecimiento_contrasena', function (Blueprint $table) {
            // El correo hace de clave primaria.
            $table->string('correo_electronico')->primary();
            $table->string('codigo');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('persona_id_created')->nullable();
            $table->unsignedBigInteger('persona_id_updated')->nullable();
        });
    }

    // down(): deshace la migración borrando las dos tablas (primero la que depende de la otra).
    public function down(): void
    {
        Schema::dropIfExists('codigos_restablecimiento_contrasena');
        Schema::dropIfExists('usuarios');
    }
};
