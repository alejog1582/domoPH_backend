<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear o actualizar el superadministrador
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@domoph.com'],
            [
                'nombre' => 'Super Administrador',
                'email' => 'admin@domoph.com',
                'password' => Hash::make('Jagr15Dmzm82Sjgz19Jsgz'),
                'activo' => true,
                'tipo_documento' => 'CC',
                'perfil' => 'superadministrador', // Asignar perfil de superadministrador
            ]
        );

        // Asignar rol de superadministrador
        $rolSuperAdmin = Role::where('slug', 'superadministrador')->first();
        
        if ($rolSuperAdmin) {
            // Verificar si ya tiene el rol asignado
            if (!$superAdmin->roles()->where('slug', 'superadministrador')->exists()) {
                $superAdmin->roles()->attach($rolSuperAdmin->id, [
                    'propiedad_id' => null // El superadmin no está asociado a una propiedad específica
                ]);
            }
            
            $this->command->info('Superadministrador creado exitosamente');
            $this->command->info('Email: admin@domoph.com');
            $this->command->info('Password: Jagr15Dmzm82Sjgz19Jsgz');
        } else {
            $this->command->error('El rol superadministrador no existe. Ejecuta primero RoleSeeder.');
        }
    }
}
