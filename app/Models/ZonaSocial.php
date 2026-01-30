<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZonaSocial extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'zonas_sociales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'propiedad_id',
        'nombre',
        'descripcion',
        'ubicacion',
        'capacidad_maxima',
        'max_invitados_por_reserva',
        'tiempo_minimo_uso_horas',
        'tiempo_maximo_uso_horas',
        'reservas_simultaneas',
        'valor_alquiler',
        'valor_deposito',
        'requiere_aprobacion',
        'permite_reservas_en_mora',
        'acepta_invitados',
        'reglamento_url',
        'estado',
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
            'capacidad_maxima' => 'integer',
            'max_invitados_por_reserva' => 'integer',
            'tiempo_minimo_uso_horas' => 'integer',
            'tiempo_maximo_uso_horas' => 'integer',
            'reservas_simultaneas' => 'integer',
            'valor_alquiler' => 'decimal:2',
            'valor_deposito' => 'decimal:2',
            'requiere_aprobacion' => 'boolean',
            'permite_reservas_en_mora' => 'boolean',
            'acepta_invitados' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los estados
     */
    const ESTADO_ACTIVA = 'activa';
    const ESTADO_INACTIVA = 'inactiva';
    const ESTADO_MANTENIMIENTO = 'mantenimiento';

    /**
     * Obtener todos los estados disponibles
     *
     * @return array<string>
     */
    public static function getEstados(): array
    {
        return [
            self::ESTADO_ACTIVA,
            self::ESTADO_INACTIVA,
            self::ESTADO_MANTENIMIENTO,
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
     * Relación con Imágenes
     */
    public function imagenes()
    {
        return $this->hasMany(ZonaSocialImagen::class)->where('activo', true)->orderBy('orden');
    }

    /**
     * Relación con todas las imágenes (incluyendo inactivas)
     */
    public function todasLasImagenes()
    {
        return $this->hasMany(ZonaSocialImagen::class)->orderBy('orden');
    }

    /**
     * Relación con Horarios
     */
    public function horarios()
    {
        return $this->hasMany(ZonaSocialHorario::class)->where('activo', true)->orderBy('dia_semana')->orderBy('hora_inicio');
    }

    /**
     * Relación con todos los horarios (incluyendo inactivos)
     */
    public function todosLosHorarios()
    {
        return $this->hasMany(ZonaSocialHorario::class)->orderBy('dia_semana')->orderBy('hora_inicio');
    }

    /**
     * Relación con Reglas
     */
    public function reglas()
    {
        return $this->hasMany(ZonaSocialRegla::class);
    }

    /**
     * Scope para zonas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true)->where('estado', self::ESTADO_ACTIVA);
    }

    /**
     * Scope para zonas de una propiedad
     */
    public function scopeDePropiedad($query, $propiedadId)
    {
        return $query->where('propiedad_id', $propiedadId);
    }

    /**
     * Scope para zonas disponibles (activas y no en mantenimiento)
     */
    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)
            ->where('estado', self::ESTADO_ACTIVA);
    }

    /**
     * Obtener una regla por su clave
     *
     * @param string $clave
     * @return ZonaSocialRegla|null
     */
    public function obtenerRegla(string $clave): ?ZonaSocialRegla
    {
        return $this->reglas()->where('clave', $clave)->first();
    }

    /**
     * Obtener el valor de una regla
     *
     * @param string $clave
     * @param mixed $default
     * @return mixed
     */
    public function obtenerValorRegla(string $clave, $default = null)
    {
        $regla = $this->obtenerRegla($clave);
        return $regla ? $regla->valor : $default;
    }
}
