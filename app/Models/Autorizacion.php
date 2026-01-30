<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Autorizacion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'autorizaciones';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'unidad_id',
        'residente_id',
        'nombre_autorizado',
        'documento_autorizado',
        'tipo_autorizado',
        'tipo_acceso',
        'placa_vehiculo',
        'dias_autorizados',
        'hora_desde',
        'hora_hasta',
        'fecha_inicio',
        'fecha_fin',
        'estado',
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
            'dias_autorizados' => 'array',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los tipos de autorizado
     */
    const TIPO_FAMILIAR = 'familiar';
    const TIPO_EMPLEADO = 'empleado';
    const TIPO_ASEO = 'aseo';
    const TIPO_MANTENIMIENTO = 'mantenimiento';
    const TIPO_PROVEEDOR = 'proveedor';
    const TIPO_OTRO = 'otro';

    /**
     * Constantes para los tipos de acceso
     */
    const ACCESO_PEATONAL = 'peatonal';
    const ACCESO_VEHICULAR = 'vehicular';
    const ACCESO_AMBOS = 'ambos';

    /**
     * Constantes para los estados
     */
    const ESTADO_ACTIVA = 'activa';
    const ESTADO_VENCIDA = 'vencida';
    const ESTADO_SUSPENDIDA = 'suspendida';

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
     * Scope para autorizaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para autorizaciones por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para autorizaciones por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_autorizado', $tipo);
    }

    /**
     * Scope para autorizaciones vigentes
     */
    public function scopeVigentes($query)
    {
        $hoy = now()->toDateString();
        return $query->where('estado', self::ESTADO_ACTIVA)
            ->where(function($q) use ($hoy) {
                $q->whereNull('fecha_inicio')
                  ->orWhere('fecha_inicio', '<=', $hoy);
            })
            ->where(function($q) use ($hoy) {
                $q->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>=', $hoy);
            });
    }

    /**
     * Verificar si la autorización está vigente
     *
     * @return bool
     */
    public function estaVigente(): bool
    {
        if ($this->estado !== self::ESTADO_ACTIVA || !$this->activo) {
            return false;
        }

        $hoy = now()->toDateString();

        if ($this->fecha_inicio && $this->fecha_inicio > $hoy) {
            return false;
        }

        if ($this->fecha_fin && $this->fecha_fin < $hoy) {
            return false;
        }

        return true;
    }

    /**
     * Verificar si la autorización aplica para un día específico
     *
     * @param string $diaSemana Nombre del día en español (ej: "lunes")
     * @return bool
     */
    public function aplicaParaDia(string $diaSemana): bool
    {
        if (empty($this->dias_autorizados)) {
            return true; // Si no hay restricción de días, aplica todos
        }

        return in_array(strtolower($diaSemana), array_map('strtolower', $this->dias_autorizados));
    }

    /**
     * Verificar si la autorización aplica para una hora específica
     *
     * @param string $hora Hora en formato H:i
     * @return bool
     */
    public function aplicaParaHora(string $hora): bool
    {
        if (!$this->hora_desde || !$this->hora_hasta) {
            return true; // Si no hay restricción de horario, aplica siempre
        }

        return $hora >= $this->hora_desde && $hora <= $this->hora_hasta;
    }

    /**
     * Obtener todos los tipos de autorizado disponibles
     *
     * @return array<string>
     */
    public static function getTiposAutorizado(): array
    {
        return [
            self::TIPO_FAMILIAR,
            self::TIPO_EMPLEADO,
            self::TIPO_ASEO,
            self::TIPO_MANTENIMIENTO,
            self::TIPO_PROVEEDOR,
            self::TIPO_OTRO,
        ];
    }

    /**
     * Obtener todos los tipos de acceso disponibles
     *
     * @return array<string>
     */
    public static function getTiposAcceso(): array
    {
        return [
            self::ACCESO_PEATONAL,
            self::ACCESO_VEHICULAR,
            self::ACCESO_AMBOS,
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
            self::ESTADO_VENCIDA,
            self::ESTADO_SUSPENDIDA,
        ];
    }
}
