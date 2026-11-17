<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nombre', 100);
            $table->string('direccion', 255);
            $table->string('codigo_postal', 10);
            $table->string('localidad', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('correo_electronico', 150)->nullable();
            $table->enum('tipo', ['propia', 'externa']);
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->unsignedBigInteger('persona_id_created')->nullable();
            $table->unsignedBigInteger('persona_id_updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes');
    }
};