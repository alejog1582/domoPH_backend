<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposito extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'depositos';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'codigo',
        'nivel',
        'estado',
        'area_m2',
        'observaciones',
        'unidad_id',
        'residente_responsable_id',
        'fecha_asignacion',
        'creado_por',
        'activo',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'area_m2' => 'decimal:2',
        'fecha_asignacion' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Relación con la copropiedad
     */
    public function copropiedad()
    {
        return $this->belongsTo(Propiedad::class, 'copropiedad_id');
    }

    /**
     * Relación con la unidad
     */
    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    /**
     * Relación con el residente responsable
     */
    public function residenteResponsable()
    {
        return $this->belongsTo(Residente::class, 'residente_responsable_id');
    }

    /**
     * Relación con el usuario que creó el registro
     */
    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Scope para obtener solo depósitos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
