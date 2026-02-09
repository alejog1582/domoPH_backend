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
            PlanSeeder::class,
            ModuloSeeder::class,
            SuperAdminSeeder::class,
            DemoSeeder::class,
            EcommerceSeeder::class,
            CarteraSeeder::class,
            ComunicadoSeeder::class,
        ]);
    }
}
