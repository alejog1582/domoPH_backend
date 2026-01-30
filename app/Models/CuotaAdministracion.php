<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuotaAdministracion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'cuotas_administracion';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'propiedad_id',
        'concepto',
        'coeficiente',
        'valor',
        'mes_desde',
        'mes_hasta',
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
            'coeficiente' => 'decimal:4',
            'valor' => 'decimal:2',
            'mes_desde' => 'date',
            'mes_hasta' => 'date',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los conceptos
     */
    const CONCEPTO_CUOTA_ORDINARIA = 'cuota_ordinaria';
    const CONCEPTO_CUOTA_EXTRAORDINARIA = 'cuota_extraordinaria';

    /**
     * Obtener todos los conceptos disponibles
     *
     * @return array<string>
     */
    public static function getConceptos(): array
    {
        return [
            self::CONCEPTO_CUOTA_ORDINARIA,
            self::CONCEPTO_CUOTA_EXTRAORDINARIA,
        ];
    }

    /**
     * Relación con Propiedad
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }

    /**
     * Scope para cuotas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para cuotas ordinarias
     */
    public function scopeOrdinarias($query)
    {
        return $query->where('concepto', self::CONCEPTO_CUOTA_ORDINARIA);
    }

    /**
     * Scope para cuotas extraordinarias
     */
    public function scopeExtraordinarias($query)
    {
        return $query->where('concepto', self::CONCEPTO_CUOTA_EXTRAORDINARIA);
    }

    /**
     * Scope para cuotas por coeficiente
     */
    public function scopePorCoeficiente($query)
    {
        return $query->whereNotNull('coeficiente');
    }

    /**
     * Scope para cuotas fijas (sin coeficiente)
     */
    public function scopeFijas($query)
    {
        return $query->whereNull('coeficiente');
    }

    /**
     * Scope para cuotas que aplican en un rango de fechas
     */
    public function scopeEnRango($query, $fecha = null)
    {
        $fecha = $fecha ?? now();
        
        return $query->where(function($q) use ($fecha) {
            $q->where(function($subQ) use ($fecha) {
                // Cuotas indefinidas (sin rango)
                $subQ->whereNull('mes_desde')
                    ->whereNull('mes_hasta');
            })->orWhere(function($subQ) use ($fecha) {
                // Cuotas con rango que incluyen la fecha
                $subQ->where('mes_desde', '<=', $fecha)
                    ->where('mes_hasta', '>=', $fecha);
            });
        });
    }

    /**
     * Verificar si la cuota es por coeficiente
     *
     * @return bool
     */
    public function esPorCoeficiente(): bool
    {
        return !is_null($this->coeficiente);
    }

    /**
     * Verificar si la cuota es fija
     *
     * @return bool
     */
    public function esFija(): bool
    {
        return is_null($this->coeficiente);
    }

    /**
     * Verificar si la cuota es indefinida (sin rango de fechas)
     *
     * @return bool
     */
    public function esIndefinida(): bool
    {
        return is_null($this->mes_desde) && is_null($this->mes_hasta);
    }

    /**
     * Verificar si la cuota aplica en una fecha específica
     *
     * @param \Carbon\Carbon|string|null $fecha
     * @return bool
     */
    public function aplicaEnFecha($fecha = null): bool
    {
        if ($this->esIndefinida()) {
            return true;
        }

        $fecha = $fecha ? \Carbon\Carbon::parse($fecha) : now();
        
        return $fecha->gte($this->mes_desde) && $fecha->lte($this->mes_hasta);
    }

    /**
     * Calcular el valor de la cuota para una unidad específica
     *
     * @param Unidad $unidad
     * @return float
     */
    public function calcularParaUnidad(Unidad $unidad): float
    {
        // Si la cuota tiene un coeficiente asignado, el valor ya está calculado para ese coeficiente específico
        // Por lo tanto, se devuelve el valor directamente sin multiplicar
        if ($this->esPorCoeficiente()) {
            // El valor en la tabla ya corresponde al coeficiente de esta cuota
            return $this->valor;
        } else {
            // Cuota fija por unidad (sin coeficiente, aplica a todas)
            return $this->valor;
        }
    }
}
