<?php

// Dirección del archivo dentro del proyecto: carpeta app/Models.
namespace App\Models\Tarifas;

// Piezas de Laravel que usa este modelo.
use Illuminate\Database\Eloquent\Model;

// Modelo Descuentos: representa la tabla "descuentos".
// Cada empresa tiene los suyos: "Hermanos 10 %", "Familia numerosa 15 €"...
// Se eligen al matricular (inscripciones.descuento_id) y la factura copia el importe descontado
// en cada línea (lineas_factura.descuento_importe), así que cambiar un descuento no cambia
// las facturas ya emitidas. No se borran: se desactivan.
class Descuentos extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'descuentos';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'tipo',               // 'porcentaje' (10 = un 10 %) o 'importe' (10 = 10 euros).
        'valor',
        'activo',
        'persona_id_created',
        'persona_id_updated',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    // Cómo se lee: "10 %" o "15,00 €".
    public function valorLegible(): string
    {
        return $this->tipo === 'porcentaje'
            ? rtrim(rtrim(number_format((float) $this->valor, 2, ',', '.'), '0'), ',') . ' %'
            : number_format((float) $this->valor, 2, ',', '.') . ' €';
    }
}
