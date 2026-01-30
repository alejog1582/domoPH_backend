<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Propiedad extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla (sin pluralización automática)
     */
    protected $table = 'propiedades';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'nit',
        'direccion',
        'ciudad',
        'departamento',
        'codigo_postal',
        'telefono',
        'email',
        'logo',
        'color_primario',
        'color_secundario',
        'descripcion',
        'total_unidades',
        'estado',
        'plan_id',
        'fecha_inicio_suscripcion',
        'fecha_fin_suscripcion',
        'trial_activo',
        'fecha_fin_trial',
        'configuracion_personalizada',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trial_activo' => 'boolean',
            'fecha_inicio_suscripcion' => 'date',
            'fecha_fin_suscripcion' => 'date',
            'fecha_fin_trial' => 'date',
            'configuracion_personalizada' => 'array',
        ];
    }

    /**
     * Relación con Plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relación con Unidades
     */
    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }

    /**
     * Relación con Carteras
     */
    public function carteras()
    {
        return $this->hasMany(Cartera::class, 'copropiedad_id');
    }

    /**
     * Relación con Cuentas de Cobro
     */
    public function cuentasCobro()
    {
        return $this->hasMany(CuentaCobro::class, 'copropiedad_id');
    }

    /**
     * Relación con Recaudos
     */
    public function recaudos()
    {
        return $this->hasMany(Recaudo::class, 'copropiedad_id');
    }

    /**
     * Relación con Comunicados
     */
    public function comunicados()
    {
        return $this->hasMany(Comunicado::class, 'copropiedad_id');
    }

    /**
     * Relación con Correspondencias
     */
    public function correspondencias()
    {
        return $this->hasMany(Correspondencia::class, 'copropiedad_id');
    }

    /**
     * Relación con Visitas
     */
    public function visitas()
    {
        return $this->hasMany(Visita::class, 'copropiedad_id');
    }

    /**
     * Relación con Autorizaciones
     */
    public function autorizaciones()
    {
        return $this->hasMany(Autorizacion::class, 'copropiedad_id');
    }

    /**
     * Relación con Acuerdos de Pago
     */
    public function acuerdosPagos()
    {
        return $this->hasMany(AcuerdoPago::class, 'copropiedad_id');
    }

    /**
     * Relación con Zonas Sociales
     */
    public function zonasSociales()
    {
        return $this->hasMany(ZonaSocial::class);
    }

    /**
     * Relación con Cuotas de Administración
     */
    public function cuotasAdministracion()
    {
        return $this->hasMany(CuotaAdministracion::class);
    }

    /**
     * Relación con Suscripciones
     */
    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }

    /**
     * Relación con Suscripción activa
     */
    public function suscripcionActiva()
    {
        return $this->hasOne(Suscripcion::class)->where('estado', 'activa')->latest();
    }

    /**
     * Relación con AdministradoresPropiedad
     */
    public function administradores()
    {
        return $this->hasMany(AdministradorPropiedad::class);
    }

    /**
     * Relación muchos a muchos con Módulos
     */
    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'propiedad_modulos')
                    ->withPivot('activo', 'fecha_activacion', 'fecha_desactivacion', 'configuracion')
                    ->withTimestamps();
    }

    /**
     * Relación con ConfiguracionesPropiedad
     */
    public function configuraciones()
    {
        return $this->hasMany(ConfiguracionPropiedad::class);
    }

    /**
     * Relación con LogsAuditoria
     */
    public function logsAuditoria()
    {
        return $this->hasMany(LogAuditoria::class);
    }

    /**
     * Relación muchos a muchos con Users a través de role_user
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

    /**
     * Scope para propiedades activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    /**
     * Verificar si tiene un módulo activo
     */
    public function tieneModuloActivo($moduloSlug)
    {
        return $this->modulos()
            ->where('slug', $moduloSlug)
            ->wherePivot('activo', true)
            ->exists();
    }
}
