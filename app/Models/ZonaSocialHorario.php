<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZonaSocialHorario extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'zona_social_horarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'zona_social_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
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
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los días de la semana
     */
    const DIA_LUNES = 'lunes';
    const DIA_MARTES = 'martes';
    const DIA_MIERCOLES = 'miércoles';
    const DIA_JUEVES = 'jueves';
    const DIA_VIERNES = 'viernes';
    const DIA_SABADO = 'sábado';
    const DIA_DOMINGO = 'domingo';

    /**
     * Obtener todos los días de la semana disponibles
     *
     * @return array<string>
     */
    public static function getDiasSemana(): array
    {
        return [
            self::DIA_LUNES,
            self::DIA_MARTES,
            self::DIA_MIERCOLES,
            self::DIA_JUEVES,
            self::DIA_VIERNES,
            self::DIA_SABADO,
            self::DIA_DOMINGO,
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
     * Scope para horarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para un día específico
     */
    public function scopeDelDia($query, string $diaSemana)
    {
        return $query->where('dia_semana', $diaSemana);
    }

    /**
     * Scope para ordenar por día y hora
     */
    public function scopeOrdenados($query)
    {
        return $query->orderByRaw("
            CASE dia_semana
                WHEN 'lunes' THEN 1
                WHEN 'martes' THEN 2
                WHEN 'miércoles' THEN 3
                WHEN 'jueves' THEN 4
                WHEN 'viernes' THEN 5
                WHEN 'sábado' THEN 6
                WHEN 'domingo' THEN 7
            END
        ")->orderBy('hora_inicio');
    }
}
