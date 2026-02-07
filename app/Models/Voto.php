<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voto extends Model
{
    use HasFactory;

    protected $table = 'votos';

    public $timestamps = false;

    protected $fillable = [
        'votacion_id',
        'residente_id',
        'opcion_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relación con Votación
     */
    public function votacion()
    {
        return $this->belongsTo(Votacion::class);
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
        return $this->belongsTo(VotacionOpcion::class, 'opcion_id');
    }
}
