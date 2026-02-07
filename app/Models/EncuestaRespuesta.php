<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncuestaRespuesta extends Model
{
    use HasFactory;

    protected $table = 'encuesta_respuestas';

    public $timestamps = false;

    protected $fillable = [
        'encuesta_id',
        'residente_id',
        'opcion_id',
        'respuesta_abierta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
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
     * Relación con Residente
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    /**
     * Relación con Opción
     */
    public function opcion()
    {
        return $this->belongsTo(EncuestaOpcion::class, 'opcion_id');
    }
}
