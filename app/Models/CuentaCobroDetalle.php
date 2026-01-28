<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaCobroDetalle extends Model
{
    /** @use HasFactory<\Database\Factories\CuentaCobroDetalleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'cuenta_cobro_detalles';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cuenta_cobro_id',
        'concepto',
        'cuota_administracion_id',
        'valor',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
        ];
    }

    /**
     * Relación con CuentaCobro
     */
    public function cuentaCobro()
    {
        return $this->belongsTo(CuentaCobro::class);
    }

    /**
     * Relación opcional con CuotaAdministracion
     */
    public function cuotaAdministracion()
    {
        return $this->belongsTo(CuotaAdministracion::class);
    }

    /**
     * Relación con RecaudoDetalle (pagos aplicados a este concepto)
     */
    public function recaudoDetalles()
    {
        return $this->hasMany(RecaudoDetalle::class);
    }
}
