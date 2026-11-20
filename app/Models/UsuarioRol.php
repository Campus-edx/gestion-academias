<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo UsuarioRol: representa la tabla "usuario_rol".
// Cada fila dice: "este usuario tiene este rol, desde esta fecha (y hasta esta otra)".
// Si centro_id está relleno, el rol solo vale en ese centro; si está vacío, en todos.
// Un rol no se borra nunca: se CIERRA poniendo fecha_fin. Así queda el historial.
// OJO: el id de esta tabla es lo que todos guardan en persona_id_created y persona_id_updated.
class UsuarioRol extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'usuario_rol';

    protected $fillable = [
        'usuario_id',
        'rol_id',
        'centro_id',          // Opcional: vacío = en todos los centros de la empresa.
        'fecha_inicio',
        'fecha_fin',          // Vacía = sin fecha de fin (sigue abierto).
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    // El rol de esta fila: $usuarioRol->rol->nombre
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'rol_id');
    }

    // SCOPE "vigentes": solo los roles que valen HOY (ya empezados y sin terminar).
    // Un scope es un filtro con nombre: se usa como $usuario->roles()->vigentes()->get().
    // Laravel quita "scope" del nombre: scopeVigentes() → vigentes().
    public function scopeVigentes(Builder $consulta): Builder
    {
        $hoy = now()->toDateString();

        return $consulta
            ->where('fecha_inicio', '<=', $hoy)
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy));
    }

    // ¿Vale hoy este rol? Para pintar la etiqueta "Vigente" en la ficha.
    public function estaVigente(): bool
    {
        return $this->fecha_inicio->lte(today())
            && ($this->fecha_fin === null || $this->fecha_fin->gte(today()));
    }
}