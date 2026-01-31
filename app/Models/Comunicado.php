<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comunicado extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'comunicados';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'titulo',
        'slug',
        'contenido',
        'resumen',
        'tipo',
        'publicado',
        'destacado',
        'fecha_publicacion',
        'visible_para',
        'imagen_portada',
        'autor_id',
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
            'publicado' => 'boolean',
            'destacado' => 'boolean',
            'fecha_publicacion' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los tipos
     */
    const TIPO_GENERAL = 'general';
    const TIPO_URGENTE = 'urgente';
    const TIPO_INFORMATIVO = 'informativo';
    const TIPO_MANTENIMIENTO = 'mantenimiento';

    /**
     * Constantes para visibilidad
     */
    const VISIBLE_TODOS = 'todos';
    const VISIBLE_PROPIETARIOS = 'propietarios';
    const VISIBLE_ARRENDATARIOS = 'arrendatarios';
    const VISIBLE_ADMINISTRACION = 'administracion';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar slug automáticamente si no se proporciona
        static::creating(function ($comunicado) {
            if (empty($comunicado->slug)) {
                $comunicado->slug = Str::slug($comunicado->titulo);
                
                // Asegurar unicidad del slug
                $originalSlug = $comunicado->slug;
                $count = 1;
                while (static::where('copropiedad_id', $comunicado->copropiedad_id)
                    ->where('slug', $comunicado->slug)
                    ->exists()) {
                    $comunicado->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Relación con Propiedad (Copropiedad)
     */
    public function copropiedad()
    {
        return $this->belongsTo(Propiedad::class, 'copropiedad_id');
    }

    /**
     * Relación con User (Autor)
     */
    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    /**
     * Relación many-to-many con Unidades
     */
    public function unidades()
    {
        return $this->belongsToMany(Unidad::class, 'comunicado_unidad')
            ->withPivot('leido', 'fecha_lectura')
            ->withTimestamps();
    }

    /**
     * Relación many-to-many con Residentes
     */
    public function residentes()
    {
        return $this->belongsToMany(Residente::class, 'comunicado_residente')
            ->withPivot('leido', 'fecha_lectura')
            ->withTimestamps();
    }

    /**
     * Scope para comunicados publicados
     */
    public function scopePublicados($query)
    {
        return $query->where('publicado', true);
    }

    /**
     * Scope para comunicados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para comunicados por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para comunicados visibles para un tipo de usuario
     */
    public function scopeVisiblesPara($query, $tipoUsuario)
    {
        return $query->where(function($q) use ($tipoUsuario) {
            $q->where('visible_para', 'todos')
              ->orWhere('visible_para', $tipoUsuario);
        });
    }

    /**
     * Obtener todos los tipos disponibles
     *
     * @return array<string>
     */
    public static function getTipos(): array
    {
        return [
            self::TIPO_GENERAL,
            self::TIPO_URGENTE,
            self::TIPO_INFORMATIVO,
            self::TIPO_MANTENIMIENTO,
        ];
    }

    /**
     * Obtener todas las opciones de visibilidad
     *
     * @return array<string>
     */
    public static function getOpcionesVisibilidad(): array
    {
        return [
            self::VISIBLE_TODOS,
            self::VISIBLE_PROPIETARIOS,
            self::VISIBLE_ARRENDATARIOS,
            self::VISIBLE_ADMINISTRACION,
        ];
    }
}
