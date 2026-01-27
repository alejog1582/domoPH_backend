<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidad extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'unidades';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'propiedad_id',
        'numero',
        'torre',
        'bloque',
        'tipo',
        'area_m2',
        'coeficiente',
        'habitaciones',
        'banos',
        'estado',
        'observaciones',
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
            'area_m2' => 'decimal:2',
            'coeficiente' => 'integer',
            'habitaciones' => 'integer',
            'banos' => 'integer',
            'caracteristicas' => 'array',
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
     * Relación con Residentes
     */
    public function residentes()
    {
        return $this->hasMany(Residente::class);
    }

    /**
     * Obtener el residente principal
     */
    public function residentePrincipal()
    {
        return $this->hasOne(Residente::class)->where('es_principal', true);
    }

    /**
     * Relación con Mascotas
     */
    public function mascotas()
    {
        return $this->hasMany(Mascota::class);
    }

    /**
     * Scope para unidades ocupadas
     */
    public function scopeOcupadas($query)
    {
        return $query->where('estado', 'ocupada');
    }
}
