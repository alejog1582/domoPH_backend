<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfertaLicitacion extends Model
{
    use HasFactory;

    protected $table = 'ofertas_licitacion';

    protected $fillable = [
        'licitacion_id',
        'nombre_proveedor',
        'nit_proveedor',
        'email_contacto',
        'telefono_contacto',
        'descripcion_oferta',
        'valor_ofertado',
        'estado',
        'fecha_postulacion',
        'es_ganadora',
    ];

    protected function casts(): array
    {
        return [
            'valor_ofertado' => 'decimal:2',
            'fecha_postulacion' => 'date',
            'es_ganadora' => 'boolean',
        ];
    }

    /**
     * Relación con Licitación
     */
    public function licitacion()
    {
        return $this->belongsTo(Licitacion::class, 'licitacion_id');
    }

    /**
     * Relación con Archivos de Oferta
     */
    public function archivos()
    {
        return $this->hasMany(OfertaArchivo::class, 'oferta_licitacion_id');
    }
}
