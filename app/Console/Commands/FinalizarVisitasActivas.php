<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Visita;
use Carbon\Carbon;

class FinalizarVisitasActivas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitas:finalizar-activas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finaliza todas las visitas que están en estado activa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de finalización de visitas activas...');

        // Obtener todas las visitas activas
        $visitas = Visita::where('estado', Visita::ESTADO_ACTIVA)
            ->where('activo', true)
            ->get();

        if ($visitas->isEmpty()) {
            $this->info('No se encontraron visitas activas para finalizar.');
            return 0;
        }

        $this->info("Se encontraron {$visitas->count()} visita(s) activa(s).");

        $finalizadas = 0;
        $errores = 0;

        foreach ($visitas as $visita) {
            try {
                // Cambiar estado a finalizada
                $visita->estado = Visita::ESTADO_FINALIZADA;
                
                // Si no tiene fecha de salida, establecerla como ahora
                if (!$visita->fecha_salida) {
                    $visita->fecha_salida = Carbon::now();
                }
                
                $visita->save();
                
                $finalizadas++;
                $this->line("✓ Visita ID {$visita->id} ({$visita->nombre_visitante}) finalizada correctamente.");
                
            } catch (\Exception $e) {
                $errores++;
                $this->error("✗ Error al finalizar visita ID {$visita->id}: " . $e->getMessage());
                \Log::error("Error al finalizar visita ID {$visita->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Proceso completado:");
        $this->info("  - Visitas finalizadas: {$finalizadas}");
        
        if ($errores > 0) {
            $this->warn("  - Errores: {$errores}");
        }

        return 0;
    }
}
