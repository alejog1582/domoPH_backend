<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfertaArchivo extends Model
{
    use HasFactory;

    protected $table = 'ofertas_archivos';

    protected $fillable = [
        'oferta_licitacion_id',
        'nombre_archivo',
        'url_archivo',
        'tipo_archivo',
    ];

    /**
     * Relación con Oferta
     */
    public function oferta()
    {
        return $this->belongsTo(OfertaLicitacion::class, 'oferta_licitacion_id');
    }
}
