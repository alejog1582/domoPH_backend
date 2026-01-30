<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pqrs extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'pqrs';

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
        'categoria',
        'asunto',
        'descripcion',
        'prioridad',
        'estado',
        'canal',
        'numero_radicado',
        'fecha_radicacion',
        'fecha_respuesta',
        'respuesta',
        'respondido_por',
        'adjuntos',
        'calificacion_servicio',
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
            'fecha_radicacion' => 'datetime',
            'fecha_respuesta' => 'datetime',
            'adjuntos' => 'array',
            'calificacion_servicio' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los tipos
     */
    const TIPO_PETICION = 'peticion';
    const TIPO_QUEJA = 'queja';
    const TIPO_RECLAMO = 'reclamo';
    const TIPO_SUGERENCIA = 'sugerencia';

    /**
     * Constantes para las categorías
     */
    const CATEGORIA_ADMINISTRACION = 'administracion';
    const CATEGORIA_MANTENIMIENTO = 'mantenimiento';
    const CATEGORIA_SEGURIDAD = 'seguridad';
    const CATEGORIA_CONVIVENCIA = 'convivencia';
    const CATEGORIA_SERVICIOS = 'servicios';
    const CATEGORIA_OTRO = 'otro';

    /**
     * Constantes para las prioridades
     */
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_CRITICA = 'critica';

    /**
     * Constantes para los estados
     */
    const ESTADO_RADICADA = 'radicada';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_RESPONDIDA = 'respondida';
    const ESTADO_CERRADA = 'cerrada';
    const ESTADO_RECHAZADA = 'rechazada';

    /**
     * Constantes para los canales
     */
    const CANAL_APP = 'app';
    const CANAL_WEB = 'web';
    const CANAL_PORTERIA = 'porteria';
    const CANAL_EMAIL = 'email';

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
     * Relación con User (quien respondió)
     */
    public function respondidoPor()
    {
        return $this->belongsTo(User::class, 'respondido_por');
    }

    /**
     * Scope para PQRS activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para PQRS por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para PQRS por tipo
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
            self::TIPO_PETICION,
            self::TIPO_QUEJA,
            self::TIPO_RECLAMO,
            self::TIPO_SUGERENCIA,
        ];
    }

    /**
     * Obtener todas las categorías disponibles
     *
     * @return array<string>
     */
    public static function getCategorias(): array
    {
        return [
            self::CATEGORIA_ADMINISTRACION,
            self::CATEGORIA_MANTENIMIENTO,
            self::CATEGORIA_SEGURIDAD,
            self::CATEGORIA_CONVIVENCIA,
            self::CATEGORIA_SERVICIOS,
            self::CATEGORIA_OTRO,
        ];
    }

    /**
     * Obtener todas las prioridades disponibles
     *
     * @return array<string>
     */
    public static function getPrioridades(): array
    {
        return [
            self::PRIORIDAD_BAJA,
            self::PRIORIDAD_MEDIA,
            self::PRIORIDAD_ALTA,
            self::PRIORIDAD_CRITICA,
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
            self::ESTADO_RADICADA,
            self::ESTADO_EN_PROCESO,
            self::ESTADO_RESPONDIDA,
            self::ESTADO_CERRADA,
            self::ESTADO_RECHAZADA,
        ];
    }
}
