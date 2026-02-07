<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicitacionArchivo extends Model
{
    use HasFactory;

    protected $table = 'licitacion_archivos';

    protected $fillable = [
        'licitacion_id',
        'nombre_archivo',
        'url_archivo',
        'tipo_archivo',
    ];

    /**
     * Relación con Licitación
     */
    public function licitacion()
    {
        return $this->belongsTo(Licitacion::class, 'licitacion_id');
    }
}
