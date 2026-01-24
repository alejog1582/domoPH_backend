<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suscripcion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'suscripciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'propiedad_id',
        'plan_id',
        'tipo',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'fecha_proximo_pago',
        'monto',
        'metodo_pago',
        'referencia_pago',
        'notas',
        'renovacion_automatica',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_proximo_pago' => 'date',
            'monto' => 'decimal:2',
            'renovacion_automatica' => 'boolean',
        ];
    }

    /**
     * Relación con Propiedad
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }

    /**
     * Relación con Plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Scope para suscripciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    /**
     * Scope para suscripciones próximas a vencer
     */
    public function scopePorVencer($query, $dias = 7)
    {
        return $query->where('estado', 'activa')
                    ->whereBetween('fecha_fin', [now(), now()->addDays($dias)]);
    }
}
