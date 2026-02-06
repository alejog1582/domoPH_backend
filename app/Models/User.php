<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'telefono',
        'documento_identidad',
        'tipo_documento',
        'activo',
        'avatar',
        'propiedad_id',
        'perfil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación muchos a muchos con Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withPivot('propiedad_id')
                    ->withTimestamps();
    }

    /**
     * Relación con Residente (un usuario puede ser residente en múltiples unidades)
     */
    public function residentes()
    {
        return $this->hasMany(Residente::class);
    }

    /**
     * Relación con AdministradoresPropiedad
     */
    public function administracionesPropiedad()
    {
        return $this->hasMany(AdministradorPropiedad::class);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role, $propiedadId = null)
    {
        $query = $this->roles()->where('slug', $role);
        
        if ($propiedadId !== null) {
            $query->wherePivot('propiedad_id', $propiedadId);
        }
        
        return $query->exists();
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission($permission)
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    /**
     * Obtener propiedades donde el usuario tiene un rol específico
     */
    public function propiedadesConRol($roleSlug)
    {
        return Propiedad::whereHas('users', function ($query) use ($roleSlug) {
            $query->where('users.id', $this->id)
                  ->where('roles.slug', $roleSlug);
        })->get();
    }

    /**
     * Agregar un propiedad_id al campo propiedad_id del usuario
     * Si ya existe, lo agrega separado por coma
     *
     * @param int $propiedadId
     * @return void
     */
    public function agregarPropiedadId(int $propiedadId): void
    {
        $propiedadesIds = $this->getPropiedadesIds();
        
        // Si el ID ya existe, no hacer nada
        if (in_array($propiedadId, $propiedadesIds)) {
            return;
        }
        
        // Agregar el nuevo ID
        $propiedadesIds[] = $propiedadId;
        
        // Actualizar el campo con los IDs separados por comas
        $this->propiedad_id = implode(',', $propiedadesIds);
        $this->save();
    }

    /**
     * Obtener los IDs de propiedades como array
     *
     * @return array
     */
    public function getPropiedadesIds(): array
    {
        if (empty($this->propiedad_id)) {
            return [];
        }
        
        // Dividir por comas y limpiar espacios
        $ids = array_map('trim', explode(',', $this->propiedad_id));
        
        // Filtrar valores vacíos y convertir a enteros, luego filtrar ceros
        $ids = array_filter(array_map('intval', $ids), function($id) {
            return $id > 0;
        });
        
        return array_values($ids); // Reindexar el array
    }
}
