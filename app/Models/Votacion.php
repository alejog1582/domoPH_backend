<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Votacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'votaciones';

    protected $fillable = [
        'copropiedad_id',
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación con Propiedad
     */
    public function copropiedad()
    {
        return $this->belongsTo(Propiedad::class, 'copropiedad_id');
    }

    /**
     * Relación con Opciones
     */
    public function opciones()
    {
        return $this->hasMany(VotacionOpcion::class)->where('activo', true)->orderBy('orden');
    }

    /**
     * Relación con Votos
     */
    public function votos()
    {
        return $this->hasMany(Voto::class);
    }

    /**
     * Scope para votaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para votaciones por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
