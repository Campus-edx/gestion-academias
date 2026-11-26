<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;

// Modelo Permisos: representa la tabla "permisos".
// Cada fila es una acción que se puede permitir o no: 'alumnos.ver', 'alumnos.editar'...
// El código es "modulo.accion" y es lo que se pregunta en $this->authorize('alumnos.editar').
// Los permisos los crea el EQUIPO DE DESARROLLO (con un INSERT en el .sql), no el usuario:
// cada permiso nuevo tiene que comprobarse en algún controlador, o no serviría de nada.
// Lo que sí cambia el usuario es QUÉ ROL tiene cada permiso: la tabla rol_permiso (pantalla Permisos).
class Permisos extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'permisos';

    protected $fillable = [
        'codigo',             // 'alumnos.editar'
        'nombre',             // 'Crear y modificar alumnos y tutores' (lo que se lee en la pantalla)
        'modulo',             // 'alumnos': sirve para agruparlos en la pantalla
        'persona_id_created',
        'persona_id_updated',
    ];
}