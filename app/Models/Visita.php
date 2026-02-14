<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visita extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'visitas';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'unidad_id',
        'residente_id',
        'nombre_visitante',
        'documento_visitante',
        'tipo_visita',
        'placa_vehiculo',
        'parqueadero_id',
        'motivo',
        'fecha_ingreso',
        'fecha_salida',
        'estado',
        'registrada_por',
        'observaciones',
        'activo',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'datetime',
            'fecha_salida' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los tipos de visita
     */
    const TIPO_PEATONAL = 'peatonal';
    const TIPO_VEHICULAR = 'vehicular';

    /**
     * Constantes para los estados
     */
    const ESTADO_ACTIVA = 'activa';
    const ESTADO_FINALIZADA = 'finalizada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_BLOQUEADA = 'bloqueada';
    const ESTADO_PROGRAMADA = 'programada';

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
     * Relación con Residente
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class, 'residente_id');
    }

    /**
     * Relación con User (quien registró la visita)
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }

    /**
     * Relación con Parqueadero
     */
    public function parqueadero()
    {
        return $this->belongsTo(Parqueadero::class, 'parqueadero_id');
    }

    /**
     * Scope para visitas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para visitas por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para visitas por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_visita', $tipo);
    }

    /**
     * Scope para visitas en curso (activas sin fecha de salida)
     */
    public function scopeEnCurso($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVA)
            ->whereNull('fecha_salida');
    }

    /**
     * Obtener todos los tipos de visita disponibles
     *
     * @return array<string>
     */
    public static function getTipos(): array
    {
        return [
            self::TIPO_PEATONAL,
            self::TIPO_VEHICULAR,
        ];
    }

    /**
     * Obtener todos los estados disponibles
     *
     * @return array<string>
     */
    public static function getEstados(): array
    {
        return [
            self::ESTADO_ACTIVA,
            self::ESTADO_FINALIZADA,
            self::ESTADO_CANCELADA,
            self::ESTADO_BLOQUEADA,
            self::ESTADO_PROGRAMADA,
        ];
    }

    /**
     * Calcular la duración de la visita en minutos
     *
     * @return int|null
     */
    public function calcularDuracion(): ?int
    {
        if (!$this->fecha_ingreso || !$this->fecha_salida) {
            return null;
        }

        return $this->fecha_ingreso->diffInMinutes($this->fecha_salida);
    }
}
