<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionPropiedad extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'configuraciones_propiedad';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'propiedad_id',
        'clave',
        'valor',
        'tipo',
        'descripcion',
    ];

    /**
     * Relación con Propiedad
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }

    /**
     * Obtener el valor convertido según el tipo
     */
    public function getValorConvertidoAttribute()
    {
        return match($this->tipo) {
            'integer' => (int) $this->valor,
            'boolean' => filter_var($this->valor, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->valor, true),
            default => $this->valor,
        };
    }
}
