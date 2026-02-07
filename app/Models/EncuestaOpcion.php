<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncuestaOpcion extends Model
{
    use HasFactory;

    protected $table = 'encuesta_opciones';

    protected $fillable = [
        'encuesta_id',
        'texto_opcion',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación con Encuesta
     */
    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class);
    }

    /**
     * Relación con Respuestas
     */
    public function respuestas()
    {
        return $this->hasMany(EncuestaRespuesta::class, 'opcion_id');
    }
}
