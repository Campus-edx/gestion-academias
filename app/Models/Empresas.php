<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo Empresas: representa la tabla "empresas" (la academia que usa el ERP).
// Hereda de Model, el modelo normal de Laravel para trabajar con una tabla.
class Empresas extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'empresas';

    // Campos que se pueden rellenar de golpe con create() o update().
    protected $fillable = [
        'razon_social',        // Nombre legal de la empresa.
        'nombre_comercial',    // Nombre con el que se la conoce.
        'cif',
        'correo_electronico',
        'telefono',
        'direccion',
        'codigo_postal',
        'localidad',
        'provincia',
        'pais',
        'nombre_aplicacion',   // Nombre que se muestra en el menú y en la pestaña.
        'color_principal',     // Color de la aplicación (botones, enlaces...).
        'ruta_logo',           // Dónde está guardado el logo.
        'activo',              // Si la empresa está activa; si no, sus usuarios no pueden entrar.
        'persona_id_created',
        'persona_id_updated',
    ];

    // Convierte automáticamente algunos campos al leerlos o guardarlos.
    protected function casts(): array
    {
        return [
            'activo' => 'boolean', // Como sí/no (true/false).
        ];
    }

    // Relación: una empresa tiene muchos usuarios (columna empresa_id de la tabla usuarios).
    // Permite escribir $empresa->usuarios para obtener la lista de sus usuarios.
    // Es la otra cara de la relación empresa() del modelo Usuarios.
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuarios::class, 'empresa_id');
    }
}