<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservaInvitado extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'reservas_invitados';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reserva_id',
        'copropiedad_id',
        'residente_id',
        'nombre',
        'documento',
        'telefono',
        'tipo',
        'placa',
        'estado',
        'fecha_ingreso',
        'fecha_salida',
        'observaciones',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'datetime',
            'fecha_salida' => 'datetime',
        ];
    }

    /**
     * Constantes para los tipos
     */
    const TIPO_PEATONAL = 'peatonal';
    const TIPO_VEHICULAR = 'vehicular';

    /**
     * Constantes para los estados
     */
    const ESTADO_REGISTRADO = 'registrado';
    const ESTADO_AUTORIZADO = 'autorizado';
    const ESTADO_RECHAZADO = 'rechazado';
    const ESTADO_INGRESADO = 'ingresado';
    const ESTADO_SALIDO = 'salido';

    /**
     * Relación con Reserva
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * Relación con Propiedad (Copropiedad)
     */
    public function copropiedad()
    {
        return $this->belongsTo(Propiedad::class, 'copropiedad_id');
    }

    /**
     * Relación con Residente (si el invitado es residente)
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class, 'residente_id');
    }

    /**
     * Scope para invitados activos
     */
    public function scopeActivos($query)
    {
        return $query->whereNull('deleted_at');
    }
}
