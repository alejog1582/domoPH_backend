<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'precio_mensual',
        'precio_anual',
        'max_unidades',
        'max_usuarios',
        'max_almacenamiento_mb',
        'soporte_prioritario',
        'activo',
        'orden',
        'caracteristicas',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio_mensual' => 'decimal:2',
            'precio_anual' => 'decimal:2',
            'soporte_prioritario' => 'boolean',
            'activo' => 'boolean',
            'caracteristicas' => 'array',
        ];
    }

    /**
     * Relación con Propiedades
     */
    public function propiedades()
    {
        return $this->hasMany(Propiedad::class);
    }

    /**
     * Relación con Suscripciones
     */
    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }

    /**
     * Relación muchos a muchos con Módulos
     */
    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'plan_modulos')
                    ->withPivot('activo', 'configuracion')
                    ->withTimestamps();
    }

    /**
     * Scope para planes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para ordenar por orden
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }
}
