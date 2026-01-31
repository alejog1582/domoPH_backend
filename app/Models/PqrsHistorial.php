<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PqrsHistorial extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'pqrs_historial';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pqrs_id',
        'estado_anterior',
        'estado_nuevo',
        'comentario',
        'soporte_url',
        'cambiado_por',
        'fecha_cambio',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_cambio' => 'datetime',
        ];
    }

    /**
     * Relación con PQRS
     */
    public function pqrs()
    {
        return $this->belongsTo(Pqrs::class, 'pqrs_id');
    }

    /**
     * Relación con User (quien hizo el cambio)
     */
    public function cambiadoPor()
    {
        return $this->belongsTo(User::class, 'cambiado_por');
    }
}
