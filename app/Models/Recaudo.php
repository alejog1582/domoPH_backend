<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recaudo extends Model
{
    /** @use HasFactory<\Database\Factories\RecaudoFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'recaudos';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'unidad_id',
        'cuenta_cobro_id',
        'numero_recaudo',
        'fecha_pago',
        'tipo_pago',
        'medio_pago',
        'referencia_pago',
        'descripcion',
        'valor_pagado',
        'estado',
        'registrado_por',
        'activo',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_pago' => 'datetime',
            'valor_pagado' => 'decimal:2',
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
     * Relación con CuentaCobro (nullable)
     */
    public function cuentaCobro()
    {
        return $this->belongsTo(CuentaCobro::class, 'cuenta_cobro_id');
    }

    /**
     * Relación con User (quien registró el recaudo)
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Relación con Detalles del recaudo
     */
    public function detalles()
    {
        return $this->hasMany(RecaudoDetalle::class);
    }

    /**
     * Scope para recaudos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para recaudos por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para recaudos de una cuenta de cobro específica
     */
    public function scopePorCuentaCobro($query, $cuentaCobroId)
    {
        return $query->where('cuenta_cobro_id', $cuentaCobroId);
    }

    /**
     * Scope para abonos sin cuenta de cobro (abonos a saldo general)
     */
    public function scopeAbonosGenerales($query)
    {
        return $query->whereNull('cuenta_cobro_id');
    }
}
