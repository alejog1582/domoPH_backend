<?php

namespace Database\Seeders;

use App\Models\Cartera;
use App\Models\Propiedad;
use App\Models\Unidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarteraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opcional: desactivar restricciones de clave foránea mientras se siembra
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Obtener algunas propiedades y sus unidades
        $propiedades = Propiedad::with('unidades')->get();

        foreach ($propiedades as $propiedad) {
            foreach ($propiedad->unidades as $unidad) {
                // Evitar duplicados si ya existe cartera
                if (Cartera::where('copropiedad_id', $propiedad->id)
                    ->where('unidad_id', $unidad->id)
                    ->exists()) {
                    continue;
                }

                $saldoMora = fake()->randomFloat(2, 0, 500000);
                $saldoCorriente = fake()->randomFloat(2, 0, 1000000);
                $saldoTotal = $saldoMora + $saldoCorriente;

                Cartera::create([
                    'copropiedad_id' => $propiedad->id,
                    'unidad_id' => $unidad->id,
                    'saldo_total' => $saldoTotal,
                    'saldo_mora' => $saldoMora,
                    'saldo_corriente' => $saldoCorriente,
                    'ultima_actualizacion' => Carbon::now(),
                    'activo' => true,
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

