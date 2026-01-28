<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cartera extends Model
{
    /** @use HasFactory<\Database\Factories\CarteraFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'carteras';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'unidad_id',
        'saldo_total',
        'saldo_mora',
        'saldo_corriente',
        'ultima_actualizacion',
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
            'saldo_total' => 'decimal:2',
            'saldo_mora' => 'decimal:2',
            'saldo_corriente' => 'decimal:2',
            'ultima_actualizacion' => 'datetime',
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
}
