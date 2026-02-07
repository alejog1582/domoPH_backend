<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encuesta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'encuestas';

    protected $fillable = [
        'copropiedad_id',
        'titulo',
        'descripcion',
        'tipo_respuesta',
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
        return $this->hasMany(EncuestaOpcion::class)->where('activo', true)->orderBy('orden');
    }

    /**
     * Relación con Respuestas
     */
    public function respuestas()
    {
        return $this->hasMany(EncuestaRespuesta::class);
    }

    /**
     * Scope para encuestas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para encuestas por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
