<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiquidacionParqueaderoVisitante extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'liquidacion_parqueaderos_visitantes';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'visita_id',
        'parqueadero_id',
        'hora_llegada',
        'hora_salida',
        'minutos_totales',
        'minutos_gracia',
        'minutos_cobrados',
        'valor_minuto',
        'valor_total',
        'estado',
        'fecha_liquidacion',
        'usuario_liquidador_id',
        'metodo_pago',
        'observaciones',
        'activo',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hora_llegada' => 'datetime',
        'hora_salida' => 'datetime',
        'minutos_totales' => 'integer',
        'minutos_gracia' => 'integer',
        'minutos_cobrados' => 'integer',
        'valor_minuto' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'fecha_liquidacion' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Constantes para los estados
     */
    const ESTADO_EN_CURSO = 'en_curso';
    const ESTADO_PAGADO = 'pagado';

    /**
     * Constantes para los métodos de pago
     */
    const METODO_EFECTIVO = 'efectivo';
    const METODO_BILLETERA_VIRTUAL = 'billetera_virtual';

    /**
     * Relación con la visita
     */
    public function visita()
    {
        return $this->belongsTo(Visita::class, 'visita_id');
    }

    /**
     * Relación con el parqueadero
     */
    public function parqueadero()
    {
        return $this->belongsTo(Parqueadero::class, 'parqueadero_id');
    }

    /**
     * Relación con el usuario liquidador
     */
    public function usuarioLiquidador()
    {
        return $this->belongsTo(User::class, 'usuario_liquidador_id');
    }

    /**
     * Scope para obtener solo liquidaciones activas
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

    /**
     * Scope para obtener liquidaciones en curso
     */
    public function scopeEnCurso($query)
    {
        return $query->where('estado', self::ESTADO_EN_CURSO);
    }

    /**
     * Scope para obtener liquidaciones pagadas
     */
    public function scopePagadas($query)
    {
        return $query->where('estado', self::ESTADO_PAGADO);
    }
}
