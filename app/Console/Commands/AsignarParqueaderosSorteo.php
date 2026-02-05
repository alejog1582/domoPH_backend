<?php

namespace App\Console\Commands;

use App\Models\SorteoParqueadero;
use App\Models\ParticipanteSorteoParqueadero;
use App\Models\Parqueadero;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AsignarParqueaderosSorteo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sorteo:asignar-parqueaderos {fecha? : Fecha del sorteo a procesar (formato: Y-m-d). Si no se proporciona, usa la fecha actual}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna parqueaderos a los participantes favorecidos en sorteos cuya fecha de sorteo coincide con la fecha especificada o el día actual';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obtener la fecha del parámetro o usar la fecha actual
        $fechaParametro = $this->argument('fecha');
        
        if ($fechaParametro) {
            try {
                $fechaProcesar = Carbon::createFromFormat('Y-m-d', $fechaParametro)->startOfDay();
            } catch (\Exception $e) {
                $this->error("Error: La fecha proporcionada '{$fechaParametro}' no es válida. Use el formato Y-m-d (ejemplo: 2024-12-25)");
                return Command::FAILURE;
            }
        } else {
            $fechaProcesar = Carbon::today();
        }

        $this->info("Iniciando asignación de parqueaderos para sorteos del día: {$fechaProcesar->format('Y-m-d')}...");

        // Buscar sorteos cuya fecha_sorteo coincida con la fecha especificada
        $sorteos = SorteoParqueadero::whereDate('fecha_sorteo', $fechaProcesar)
            ->where('activo', true)
            ->get();

        if ($sorteos->isEmpty()) {
            $this->info("No se encontraron sorteos programados para la fecha {$fechaProcesar->format('Y-m-d')}.");
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$sorteos->count()} sorteo(s) para procesar.");

        $totalAsignados = 0;
        $totalOmitidos = 0;
        $totalErrores = 0;

        foreach ($sorteos as $sorteo) {
            $this->line("Procesando sorteo: {$sorteo->titulo} (ID: {$sorteo->id})");

            // Obtener participantes del sorteo
            $participantes = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $sorteo->id)
                ->where('activo', true)
                ->get();

            if ($participantes->isEmpty()) {
                $this->warn("  No se encontraron participantes para este sorteo.");
                continue;
            }

            $this->line("  Procesando {$participantes->count()} participante(s)...");

            foreach ($participantes as $participante) {
                // Verificar si el parqueadero asignado es "Balota blanca"
                if (strtolower(trim($participante->parqueadero_asignado)) === 'balota blanca') {
                    $this->line("  - Participante ID {$participante->id}: Omitido (Balota blanca)");
                    $totalOmitidos++;
                    continue;
                }

                // Buscar el parqueadero por código
                $parqueadero = Parqueadero::where('codigo', $participante->parqueadero_asignado)
                    ->where('copropiedad_id', $participante->copropiedad_id)
                    ->where('activo', true)
                    ->first();

                if (!$parqueadero) {
                    $this->error("  - Participante ID {$participante->id}: No se encontró el parqueadero con código '{$participante->parqueadero_asignado}'");
                    $totalErrores++;
                    continue;
                }

                // Verificar si el parqueadero ya está asignado a otra unidad/residente
                if ($parqueadero->unidad_id !== null && $parqueadero->unidad_id !== $participante->unidad_id) {
                    $this->warn("  - Participante ID {$participante->id}: El parqueadero '{$parqueadero->codigo}' ya está asignado a otra unidad.");
                    $totalErrores++;
                    continue;
                }

                // Asignar el parqueadero a la unidad y residente
                try {
                    $parqueadero->unidad_id = $participante->unidad_id;
                    $parqueadero->residente_responsable_id = $participante->residente_id;
                    $parqueadero->fecha_asignacion = $fechaProcesar;
                    $parqueadero->estado = 'asignado';
                    $parqueadero->save();

                    $this->info("  ✓ Participante ID {$participante->id}: Parqueadero '{$parqueadero->codigo}' asignado exitosamente.");
                    $totalAsignados++;
                } catch (\Exception $e) {
                    $this->error("  - Participante ID {$participante->id}: Error al asignar parqueadero - {$e->getMessage()}");
                    $totalErrores++;
                }
            }
        }

        // Resumen final
        $this->newLine();
        $this->info('=== Resumen de asignación ===');
        $this->info("Parqueaderos asignados: {$totalAsignados}");
        $this->info("Participantes omitidos (Balota blanca): {$totalOmitidos}");
        $this->info("Errores: {$totalErrores}");

        return Command::SUCCESS;
    }
}
