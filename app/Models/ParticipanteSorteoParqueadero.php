<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParticipanteSorteoParqueadero extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'participantes_sorteos_parqueadero';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sorteo_parqueadero_id',
        'copropiedad_id',
        'unidad_id',
        'residente_id',
        'tipo_vehiculo',
        'placa',
        'tarjeta_propiedad_url',
        'soat_url',
        'tecnomecanica_url',
        'parqueadero_asignado',
        'fue_favorecido',
        'fecha_inscripcion',
        'activo',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fue_favorecido' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Relación con el sorteo
     */
    public function sorteo()
    {
        return $this->belongsTo(SorteoParqueadero::class, 'sorteo_parqueadero_id');
    }

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
     * Relación con el residente
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class, 'residente_id');
    }

    /**
     * Scope para obtener solo participantes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por tipo de vehículo
     */
    public function scopePorTipoVehiculo($query, $tipo)
    {
        return $query->where('tipo_vehiculo', $tipo);
    }

    /**
     * Scope para filtrar por favorecidos
     */
    public function scopeFavorecidos($query)
    {
        return $query->where('fue_favorecido', true);
    }
}
