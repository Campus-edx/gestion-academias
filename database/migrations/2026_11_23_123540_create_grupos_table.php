
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar la restricción de fechas existente
        DB::statement(
            'ALTER TABLE `grupos` DROP CONSTRAINT `grupos_fechas_check`'
        );

        // Eliminar los dos campos que no pertenecen
        // a la estructura definitiva
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_inicio',
                'fecha_fin'
            ]);
        });
    }

    public function down(): void
    {
        // Restaurar los campos anteriores
        Schema::table('grupos', function (Blueprint $table) {
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
        });

        // Restaurar la restricción
        DB::statement(
            'ALTER TABLE `grupos`
             ADD CONSTRAINT `grupos_fechas_check`
             CHECK (`fecha_fin` >= `fecha_inicio`)'
        );
    }
};
