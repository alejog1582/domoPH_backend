<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Superadministrador',
                'slug' => 'superadministrador',
                'descripcion' => 'Administrador del sistema SaaS domoPH. Acceso total al sistema.',
                'activo' => true,
            ],
            [
                'nombre' => 'Administrador',
                'slug' => 'administrador',
                'descripcion' => 'Administrador de una copropiedad. Gestiona su conjunto residencial.',
                'activo' => true,
            ],
            [
                'nombre' => 'Residente',
                'slug' => 'residente',
                'descripcion' => 'Residente o propietario de una unidad en la copropiedad.',
                'activo' => true,
            ],
            [
                'nombre' => 'Portería',
                'slug' => 'porteria',
                'descripcion' => 'Personal de portería y seguridad de la copropiedad.',
                'activo' => true,
            ],
            [
                'nombre' => 'Proveedor',
                'slug' => 'proveedor',
                'descripcion' => 'Proveedor de servicios para la copropiedad.',
                'activo' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }

        $this->command->info('Roles creados exitosamente');
    }
}
