<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mascota extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'mascotas';

    /**
     * Constantes para tipos de mascotas
     */
    public const TIPO_PERRO = 'perro';
    public const TIPO_GATO = 'gato';
    public const TIPO_AVE = 'ave';
    public const TIPO_REPTIL = 'reptil';
    public const TIPO_ROEDOR = 'roedor';
    public const TIPO_OTRO = 'otro';

    /**
     * Constantes para sexo
     */
    public const SEXO_MACHO = 'macho';
    public const SEXO_HEMBRA = 'hembra';
    public const SEXO_DESCONOCIDO = 'desconocido';

    /**
     * Constantes para tamaño
     */
    public const TAMANIO_PEQUENO = 'pequeño';
    public const TAMANIO_MEDIANO = 'mediano';
    public const TAMANIO_GRANDE = 'grande';

    /**
     * Constantes para estado de salud
     */
    public const ESTADO_SALUD_SALUDABLE = 'saludable';
    public const ESTADO_SALUD_EN_TRATAMIENTO = 'en_tratamiento';
    public const ESTADO_SALUD_CRONICO = 'crónico';
    public const ESTADO_SALUD_DESCONOCIDO = 'desconocido';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unidad_id',
        'residente_id',
        'nombre',
        'tipo',
        'raza',
        'color',
        'sexo',
        'fecha_nacimiento',
        'edad_aproximada',
        'peso_kg',
        'tamanio',
        'numero_chip',
        'vacunado',
        'esterilizado',
        'estado_salud',
        'foto_url',
        'foto_url_vacunas',
        'fecha_vigencia_vacunas',
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
            'fecha_nacimiento' => 'date',
            'fecha_vigencia_vacunas' => 'date',
            'edad_aproximada' => 'integer',
            'peso_kg' => 'decimal:2',
            'vacunado' => 'boolean',
            'esterilizado' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación con Unidad
     */
    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    /**
     * Relación con Residente
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    /**
     * Scope para mascotas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para mascotas por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para mascotas vacunadas
     */
    public function scopeVacunadas($query)
    {
        return $query->where('vacunado', true);
    }

    /**
     * Scope para mascotas esterilizadas
     */
    public function scopeEsterilizadas($query)
    {
        return $query->where('esterilizado', true);
    }

    /**
     * Obtener los tipos de mascotas disponibles
     */
    public static function getTipos(): array
    {
        return [
            self::TIPO_PERRO,
            self::TIPO_GATO,
            self::TIPO_AVE,
            self::TIPO_REPTIL,
            self::TIPO_ROEDOR,
            self::TIPO_OTRO,
        ];
    }

    /**
     * Obtener los sexos disponibles
     */
    public static function getSexos(): array
    {
        return [
            self::SEXO_MACHO,
            self::SEXO_HEMBRA,
            self::SEXO_DESCONOCIDO,
        ];
    }

    /**
     * Obtener los tamaños disponibles
     */
    public static function getTamanios(): array
    {
        return [
            self::TAMANIO_PEQUENO,
            self::TAMANIO_MEDIANO,
            self::TAMANIO_GRANDE,
        ];
    }

    /**
     * Obtener los estados de salud disponibles
     */
    public static function getEstadosSalud(): array
    {
        return [
            self::ESTADO_SALUD_SALUDABLE,
            self::ESTADO_SALUD_EN_TRATAMIENTO,
            self::ESTADO_SALUD_CRONICO,
            self::ESTADO_SALUD_DESCONOCIDO,
        ];
    }
}
