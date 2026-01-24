<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionGlobal extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'configuraciones_globales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'clave',
        'valor',
        'tipo',
        'descripcion',
        'categoria',
        'editable',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'editable' => 'boolean',
        ];
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

    /**
     * Scope para filtrar por categoría
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope para configuraciones editables
     */
    public function scopeEditables($query)
    {
        return $query->where('editable', true);
    }
}
