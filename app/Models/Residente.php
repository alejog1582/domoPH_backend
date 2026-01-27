<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Residente extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'residentes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'unidad_id',
        'tipo_relacion',
        'fecha_inicio',
        'fecha_fin',
        'es_principal',
        'recibe_notificaciones',
        'observaciones',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'es_principal' => 'boolean',
            'recibe_notificaciones' => 'boolean',
        ];
    }

    /**
     * Relación con User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con Unidad
     */
    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    /**
     * Scope para residentes principales
     */
    public function scopePrincipales($query)
    {
        return $query->where('es_principal', true);
    }

    /**
     * Scope para residentes activos (sin fecha_fin o fecha_fin futura)
     */
    public function scopeActivos($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('fecha_fin')
              ->orWhere('fecha_fin', '>=', now());
        });
    }

    /**
     * Relación con Mascotas
     */
    public function mascotas()
    {
        return $this->hasMany(Mascota::class);
    }
}
