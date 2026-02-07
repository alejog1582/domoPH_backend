<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Licitacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'licitaciones';

    protected $fillable = [
        'copropiedad_id',
        'titulo',
        'descripcion',
        'categoria',
        'presupuesto_estimado',
        'fecha_publicacion',
        'fecha_cierre',
        'estado',
        'visible_publicamente',
        'creado_por',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'presupuesto_estimado' => 'decimal:2',
            'fecha_publicacion' => 'date',
            'fecha_cierre' => 'date',
            'visible_publicamente' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación con Propiedad
     */
    public function copropiedad()
    {
        return $this->belongsTo(Propiedad::class, 'copropiedad_id');
    }

    /**
     * Relación con Usuario creador
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Relación con Archivos de Licitación
     */
    public function archivos()
    {
        return $this->hasMany(LicitacionArchivo::class, 'licitacion_id');
    }

    /**
     * Relación con Ofertas
     */
    public function ofertas()
    {
        return $this->hasMany(OfertaLicitacion::class, 'licitacion_id');
    }
}
