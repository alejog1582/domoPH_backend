<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudArchivo extends Model
{
    use HasFactory;

    protected $table = 'solicitud_archivos';

    protected $fillable = [
        'solicitud_comercial_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_mime',
        'tamaño',
        'cargado_por_user_id',
    ];

    protected function casts(): array
    {
        return [
            'tamaño' => 'integer',
        ];
    }

    /**
     * Relación con SolicitudComercial
     */
    public function solicitudComercial()
    {
        return $this->belongsTo(SolicitudComercial::class, 'solicitud_comercial_id');
    }

    /**
     * Relación con Usuario que cargó el archivo
     */
    public function cargadoPor()
    {
        return $this->belongsTo(User::class, 'cargado_por_user_id');
    }
}
