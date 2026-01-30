<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaHistorial extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'reservas_historial';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reserva_id',
        'estado_anterior',
        'estado_nuevo',
        'comentario',
        'cambiado_por',
        'fecha_cambio',
    ];

    /**
     * Casts de atributos
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
     * Relación con Reserva
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * Relación con User (Cambiado por)
     */
    public function cambiadoPor()
    {
        return $this->belongsTo(User::class, 'cambiado_por');
    }
}
