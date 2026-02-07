<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modulo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'modulos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'icono',
        'ruta',
        'activo',
        'requiere_configuracion',
        'orden',
        'configuracion_default',
        'es_admin',
        'es_consejo',
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
            'requiere_configuracion' => 'boolean',
            'es_admin' => 'boolean',
            'es_consejo' => 'boolean',
            'configuracion_default' => 'array',
        ];
    }

    /**
     * Relación muchos a muchos con Planes
     */
    public function planes()
    {
        return $this->belongsToMany(Plan::class, 'plan_modulos')
                    ->withPivot('activo', 'configuracion')
                    ->withTimestamps();
    }

    /**
     * Relación muchos a muchos con Propiedades
     */
    public function propiedades()
    {
        return $this->belongsToMany(Propiedad::class, 'propiedad_modulos')
                    ->withPivot('activo', 'fecha_activacion', 'fecha_desactivacion', 'configuracion')
                    ->withTimestamps();
    }

    /**
     * Scope para módulos activos
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
