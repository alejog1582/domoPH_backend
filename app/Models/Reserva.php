<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla
     */
    protected $table = 'reservas';

    /**
     * Atributos asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copropiedad_id',
        'unidad_id',
        'residente_id',
        'zona_social_id',
        'nombre_solicitante',
        'telefono_solicitante',
        'email_solicitante',
        'fecha_reserva',
        'hora_inicio',
        'hora_fin',
        'duracion_minutos',
        'cantidad_invitados',
        'descripcion',
        'costo_reserva',
        'deposito_garantia',
        'requiere_pago',
        'estado_pago',
        'estado',
        'aprobada_por',
        'fecha_aprobacion',
        'motivo_rechazo',
        'motivo_cancelacion',
        'es_exclusiva',
        'permite_invitados',
        'incumplimiento',
        'observaciones_admin',
        'adjuntos',
        'activo',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_reserva' => 'date',
            'duracion_minutos' => 'integer',
            'cantidad_invitados' => 'integer',
            'costo_reserva' => 'decimal:2',
            'deposito_garantia' => 'decimal:2',
            'requiere_pago' => 'boolean',
            'es_exclusiva' => 'boolean',
            'permite_invitados' => 'boolean',
            'incumplimiento' => 'boolean',
            'fecha_aprobacion' => 'datetime',
            'adjuntos' => 'array',
            'activo' => 'boolean',
        ];
    }

    /**
     * Constantes para los estados
     */
    const ESTADO_SOLICITADA = 'solicitada';
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_RECHAZADA = 'rechazada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_FINALIZADA = 'finalizada';

    /**
     * Constantes para estados de pago
     */
    const PAGO_PENDIENTE = 'pendiente';
    const PAGO_PAGADO = 'pagado';
    const PAGO_EXENTO = 'exento';
    const PAGO_REEMBOLSADO = 'reembolsado';

    /**
     * Relación con Propiedad (Copropiedad)
     */
    public function copropiedad()
    {
        return $this->belongsTo(Propiedad::class, 'copropiedad_id');
    }

    /**
     * Relación con Unidad
     */
    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    /**
     * Relación con Residente
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class, 'residente_id');
    }

    /**
     * Relación con ZonaSocial
     */
    public function zonaSocial()
    {
        return $this->belongsTo(ZonaSocial::class, 'zona_social_id');
    }

    /**
     * Relación con User (Aprobada por)
     */
    public function aprobadaPor()
    {
        return $this->belongsTo(User::class, 'aprobada_por');
    }

    /**
     * Relación con Invitados
     */
    public function invitados()
    {
        return $this->hasMany(ReservaInvitado::class, 'reserva_id');
    }

    /**
     * Relación con Historial
     */
    public function historial()
    {
        return $this->hasMany(ReservaHistorial::class, 'reserva_id');
    }

    /**
     * Scope para reservas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para reservas futuras
     */
    public function scopeFuturas($query)
    {
        return $query->where('fecha_reserva', '>=', now()->format('Y-m-d'));
    }

    /**
     * Scope para reservas de una copropiedad
     */
    public function scopeDeCopropiedad($query, $copropiedadId)
    {
        return $query->where('copropiedad_id', $copropiedadId);
    }
}
