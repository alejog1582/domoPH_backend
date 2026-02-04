<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SorteoParqueadero extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'sorteos_parqueadero';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'titulo',
        'descripcion',
        'fecha_inicio_recoleccion',
        'fecha_fin_recoleccion',
        'fecha_sorteo',
        'hora_sorteo',
        'fecha_inicio_uso',
        'duracion_meses',
        'capacidad_autos',
        'capacidad_motos',
        'balotas_blancas_carro',
        'balotas_blancas_moto',
        'estado',
        'creado_por',
        'activo',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio_recoleccion' => 'date',
        'fecha_fin_recoleccion' => 'date',
        'fecha_sorteo' => 'date',
        'fecha_inicio_uso' => 'date',
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
     * Relación con el usuario que creó el sorteo
     */
    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Relación con los participantes del sorteo
     */
    public function participantes()
    {
        return $this->hasMany(ParticipanteSorteoParqueadero::class, 'sorteo_parqueadero_id');
    }

    /**
     * Scope para obtener solo sorteos activos
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
