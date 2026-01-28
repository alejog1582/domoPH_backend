<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaCobro extends Model
{
    /** @use HasFactory<\Database\Factories\CuentaCobroFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'cuenta_cobros';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'unidad_id',
        'periodo',
        'fecha_emision',
        'fecha_vencimiento',
        'valor_cuotas',
        'valor_intereses',
        'valor_descuentos',
        'valor_recargos',
        'valor_total',
        'estado',
        'observaciones',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'valor_cuotas' => 'decimal:2',
            'valor_intereses' => 'decimal:2',
            'valor_descuentos' => 'decimal:2',
            'valor_recargos' => 'decimal:2',
            'valor_total' => 'decimal:2',
        ];
    }

    /**
     * Relación con Propiedad (Copropiedad)
     */
    public function copropiedad()
    {
        return $this->belongsTo(Propiedad::class, 'copropiedad_id');
    }

    /**
     * Relación con Unidad
     */
    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    /**
     * Relación con Detalles de la cuenta de cobro
     */
    public function detalles()
    {
        return $this->hasMany(CuentaCobroDetalle::class);
    }

    /**
     * Relación con Recaudos (pagos realizados)
     */
    public function recaudos()
    {
        return $this->hasMany(Recaudo::class);
    }

    /**
     * Recalcular el valor_total según los componentes
     */
    public function recalcularTotal(): void
    {
        $this->valor_total = ($this->valor_cuotas + $this->valor_intereses + $this->valor_recargos) - $this->valor_descuentos;
    }

    /**
     * Calcular el saldo pendiente de la cuenta de cobro
     */
    public function calcularSaldoPendiente(): float
    {
        $totalRecaudado = $this->recaudos()
            ->where('estado', '!=', 'anulado')
            ->sum('valor_pagado');
        
        return max(0, $this->valor_total - $totalRecaudado);
    }
}
