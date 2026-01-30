<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LlamadoAtencion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'llamados_atencion';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'unidad_id',
        'residente_id',
        'tipo',
        'motivo',
        'descripcion',
        'nivel',
        'estado',
        'fecha_evento',
        'fecha_registro',
        'registrado_por',
        'evidencia',
        'observaciones',
        'es_reincidencia',
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
            'fecha_evento' => 'datetime',
            'fecha_registro' => 'datetime',
            'evidencia' => 'array',
            'es_reincidencia' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los tipos
     */
    const TIPO_CONVIVENCIA = 'convivencia';
    const TIPO_RUIDO = 'ruido';
    const TIPO_MASCOTAS = 'mascotas';
    const TIPO_PARQUEADERO = 'parqueadero';
    const TIPO_SEGURIDAD = 'seguridad';
    const TIPO_OTRO = 'otro';

    /**
     * Constantes para los niveles
     */
    const NIVEL_LEVE = 'leve';
    const NIVEL_MODERADO = 'moderado';
    const NIVEL_GRAVE = 'grave';

    /**
     * Constantes para los estados
     */
    const ESTADO_ABIERTO = 'abierto';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_CERRADO = 'cerrado';
    const ESTADO_ANULADO = 'anulado';

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
     * Relación con User (quien registró)
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Scope para llamados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para llamados por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para llamados por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Obtener todos los tipos disponibles
     *
     * @return array<string>
     */
    public static function getTipos(): array
    {
        return [
            self::TIPO_CONVIVENCIA,
            self::TIPO_RUIDO,
            self::TIPO_MASCOTAS,
            self::TIPO_PARQUEADERO,
            self::TIPO_SEGURIDAD,
            self::TIPO_OTRO,
        ];
    }

    /**
     * Obtener todos los niveles disponibles
     *
     * @return array<string>
     */
    public static function getNiveles(): array
    {
        return [
            self::NIVEL_LEVE,
            self::NIVEL_MODERADO,
            self::NIVEL_GRAVE,
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
            self::ESTADO_ABIERTO,
            self::ESTADO_EN_PROCESO,
            self::ESTADO_CERRADO,
            self::ESTADO_ANULADO,
        ];
    }
}
