<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VotacionOpcion extends Model
{
    use HasFactory;

    protected $table = 'votacion_opciones';

    protected $fillable = [
        'votacion_id',
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
     * Relación con Votación
     */
    public function votacion()
    {
        return $this->belongsTo(Votacion::class);
    }

    /**
     * Relación con Votos
     */
    public function votos()
    {
        return $this->hasMany(Voto::class, 'opcion_id');
    }
}
