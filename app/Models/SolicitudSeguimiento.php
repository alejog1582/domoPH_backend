<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudSeguimiento extends Model
{
    use HasFactory;

    protected $table = 'solicitud_seguimientos';

    protected $fillable = [
        'solicitud_comercial_id',
        'user_id',
        'comentario',
        'estado_resultante',
        'proximo_contacto',
    ];

    protected function casts(): array
    {
        return [
            'proximo_contacto' => 'datetime',
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
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
