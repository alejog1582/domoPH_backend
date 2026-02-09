<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudComercial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes_comerciales';

    protected $fillable = [
        'tipo_solicitud',
        'nombre_contacto',
        'empresa',
        'email',
        'telefono',
        'ciudad',
        'pais',
        'mensaje',
        'origen',
        'estado_gestion',
        'prioridad',
        'fecha_contacto',
        'asignado_a_user_id',
        'metadata',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_contacto' => 'datetime',
            'metadata' => 'array',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación con Usuario asignado
     */
    public function asignadoA()
    {
        return $this->belongsTo(User::class, 'asignado_a_user_id');
    }

    /**
     * Relación con Seguimientos
     */
    public function seguimientos()
    {
        return $this->hasMany(SolicitudSeguimiento::class, 'solicitud_comercial_id');
    }

    /**
     * Relación con Archivos
     */
    public function archivos()
    {
        return $this->hasMany(SolicitudArchivo::class, 'solicitud_comercial_id');
    }
}
