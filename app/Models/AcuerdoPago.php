<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcuerdoPago extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'acuerdos_pagos';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'unidad_id',
        'cartera_id',
        'cuenta_cobro_id',
        'numero_acuerdo',
        'fecha_acuerdo',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'saldo_original',
        'valor_acordado',
        'valor_inicial',
        'saldo_pendiente',
        'numero_cuotas',
        'valor_cuota',
        'valor_mensual_propuesto',
        'interes_acuerdo',
        'valor_intereses',
        'estado',
        'activo',
        'usuario_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_acuerdo' => 'date',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'saldo_original' => 'decimal:2',
            'valor_acordado' => 'decimal:2',
            'valor_inicial' => 'decimal:2',
            'saldo_pendiente' => 'decimal:2',
            'numero_cuotas' => 'integer',
            'valor_cuota' => 'decimal:2',
            'valor_mensual_propuesto' => 'decimal:2',
            'interes_acuerdo' => 'decimal:2',
            'valor_intereses' => 'decimal:2',
            'activo' => 'boolean',
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
     * Relación con Cartera
     */
    public function cartera()
    {
        return $this->belongsTo(Cartera::class, 'cartera_id');
    }

    /**
     * Relación con CuentaCobro (opcional)
     */
    public function cuentaCobro()
    {
        return $this->belongsTo(CuentaCobro::class, 'cuenta_cobro_id');
    }

    /**
     * Relación con User (usuario que registró el acuerdo)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
