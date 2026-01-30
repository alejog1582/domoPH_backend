<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Correspondencia extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'correspondencias';

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
        'descripcion',
        'remitente',
        'numero_guia',
        'estado',
        'fecha_recepcion',
        'fecha_entrega',
        'recibido_por',
        'entregado_a',
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
            'fecha_recepcion' => 'datetime',
            'fecha_entrega' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los tipos
     */
    const TIPO_PAQUETE = 'paquete';
    const TIPO_DOCUMENTO = 'documento';
    const TIPO_FACTURA = 'factura';
    const TIPO_DOMICILIO = 'domicilio';
    const TIPO_OTRO = 'otro';

    /**
     * Constantes para los estados
     */
    const ESTADO_RECIBIDO = 'recibido';
    const ESTADO_ENTREGADO = 'entregado';
    const ESTADO_DEVUELTO = 'devuelto';
    const ESTADO_PERDIDO = 'perdido';

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
     * Scope para correspondencias activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para correspondencias por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para correspondencias por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para correspondencias pendientes (recibidas pero no entregadas)
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_RECIBIDO);
    }

    /**
     * Obtener todos los tipos disponibles
     *
     * @return array<string>
     */
    public static function getTipos(): array
    {
        return [
            self::TIPO_PAQUETE,
            self::TIPO_DOCUMENTO,
            self::TIPO_FACTURA,
            self::TIPO_DOMICILIO,
            self::TIPO_OTRO,
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
            self::ESTADO_RECIBIDO,
            self::ESTADO_ENTREGADO,
            self::ESTADO_DEVUELTO,
            self::ESTADO_PERDIDO,
        ];
    }
}
