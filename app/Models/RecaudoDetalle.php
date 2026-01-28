<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecaudoDetalle extends Model
{
    /** @use HasFactory<\Database\Factories\RecaudoDetalleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'recaudo_detalles';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recaudo_id',
        'cuenta_cobro_detalle_id',
        'concepto',
        'valor_aplicado',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor_aplicado' => 'decimal:2',
        ];
    }

    /**
     * Relación con Recaudo
     */
    public function recaudo()
    {
        return $this->belongsTo(Recaudo::class);
    }

    /**
     * Relación con CuentaCobroDetalle (nullable)
     */
    public function cuentaCobroDetalle()
    {
        return $this->belongsTo(CuentaCobroDetalle::class);
    }
}
