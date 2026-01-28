<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZonaSocialRegla extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'zona_social_reglas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'zona_social_id',
        'clave',
        'valor',
        'descripcion',
    ];

    /**
     * Constantes para claves comunes de reglas
     */
    const CLAVE_MAX_RESERVAS_MES = 'max_reservas_mes';
    const CLAVE_REQUIERE_DEPOSITO = 'requiere_deposito';
    const CLAVE_BLOQUEAR_EN_MORA = 'bloquear_en_mora';
    const CLAVE_DIAS_ANTICIPACION = 'dias_anticipacion';
    const CLAVE_HORAS_CANCELACION = 'horas_cancelacion';
    const CLAVE_PERMITE_INVITADOS = 'permite_invitados';
    const CLAVE_MAX_INVITADOS = 'max_invitados';

    /**
     * Obtener todas las claves comunes de reglas
     *
     * @return array<string>
     */
    public static function getClavesComunes(): array
    {
        return [
            self::CLAVE_MAX_RESERVAS_MES,
            self::CLAVE_REQUIERE_DEPOSITO,
            self::CLAVE_BLOQUEAR_EN_MORA,
            self::CLAVE_DIAS_ANTICIPACION,
            self::CLAVE_HORAS_CANCELACION,
            self::CLAVE_PERMITE_INVITADOS,
            self::CLAVE_MAX_INVITADOS,
        ];
    }

    /**
     * Relación con ZonaSocial
     */
    public function zonaSocial()
    {
        return $this->belongsTo(ZonaSocial::class);
    }

    /**
     * Scope para una clave específica
     */
    public function scopeDeClave($query, string $clave)
    {
        return $query->where('clave', $clave);
    }
}
