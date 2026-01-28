<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            SuperAdminSeeder::class,
            PlanSeeder::class,
            ModuloSeeder::class,
            CarteraSeeder::class,
            CuentaCobroSeeder::class,
            ConfiguracionPropiedadSeeder::class,
            RecaudoSeeder::class,
        ]);
    }
}
