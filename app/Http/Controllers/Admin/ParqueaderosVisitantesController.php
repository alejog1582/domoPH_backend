<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parqueadero;
use App\Models\Unidad;
use App\Models\Residente;
use App\Models\Visita;
use App\Models\LiquidacionParqueaderoVisitante;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParqueaderosVisitantesController extends Controller
{
    /**
     * Mostrar la lista de parqueaderos de visitantes
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener parqueaderos de visitantes
        $parqueaderos = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('tipo', 'visitantes')
            ->where('activo', true)
            ->orderBy('tipo_vehiculo')
            ->orderBy('codigo')
            ->get();

        // Agrupar por tipo_vehiculo y estado
        $parqueaderosAgrupados = [
            'carro' => [
                'disponible' => $parqueaderos->where('tipo_vehiculo', 'carro')->where('estado', 'disponible'),
                'ocupado' => $parqueaderos->where('tipo_vehiculo', 'carro')->where('estado', 'ocupado'),
            ],
            'moto' => [
                'disponible' => $parqueaderos->where('tipo_vehiculo', 'moto')->where('estado', 'disponible'),
                'ocupado' => $parqueaderos->where('tipo_vehiculo', 'moto')->where('estado', 'ocupado'),
            ],
        ];

        // Obtener unidades y residentes para el modal
        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get();

        $residentes = Residente::whereHas('unidad', function($q) use ($propiedad) {
            $q->where('propiedad_id', $propiedad->id);
        })
        ->activos()
        ->with('user')
        ->join('users', 'residentes.user_id', '=', 'users.id')
        ->orderBy('users.nombre')
        ->select('residentes.*')
        ->get();

        return view('admin.parqueaderos-visitantes.index', compact('parqueaderosAgrupados', 'propiedad', 'unidades', 'residentes'));
    }

    /**
     * Guardar una nueva visita desde el modal
     */
    public function storeVisita(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No hay propiedad asignada.'
            ], 400);
        }

        $validated = $request->validate([
            'unidad_id' => 'required|exists:unidades,id',
            'residente_id' => 'nullable|exists:residentes,id',
            'nombre_visitante' => 'required|string|max:150',
            'documento_visitante' => 'nullable|string|max:50',
            'tipo_visita' => 'required|in:peatonal,vehicular',
            'placa_vehiculo' => 'nullable|string|max:20|required_if:tipo_visita,vehicular',
            'parqueadero_id' => 'required|exists:parqueaderos,id',
            'motivo' => 'nullable|string|max:200',
            'fecha_ingreso' => 'required|date',
            'observaciones' => 'nullable|string',
        ], [
            'unidad_id.required' => 'La unidad es obligatoria.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'nombre_visitante.required' => 'El nombre del visitante es obligatorio.',
            'tipo_visita.required' => 'El tipo de visita es obligatorio.',
            'tipo_visita.in' => 'El tipo de visita seleccionado no es válido.',
            'placa_vehiculo.required_if' => 'La placa del vehículo es obligatoria para visitas vehiculares.',
            'parqueadero_id.required' => 'El parqueadero es obligatorio.',
            'parqueadero_id.exists' => 'El parqueadero seleccionado no existe.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que la unidad pertenezca a la propiedad
            $unidad = Unidad::where('propiedad_id', $propiedad->id)
                ->where('id', $validated['unidad_id'])
                ->first();

            if (!$unidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'La unidad no pertenece a la propiedad activa.'
                ], 400);
            }

            // Verificar que el parqueadero pertenezca a la propiedad y esté disponible
            $parqueadero = Parqueadero::where('copropiedad_id', $propiedad->id)
                ->where('id', $validated['parqueadero_id'])
                ->where('tipo', 'visitantes')
                ->where('estado', 'disponible')
                ->where('activo', true)
                ->first();

            if (!$parqueadero) {
                return response()->json([
                    'success' => false,
                    'message' => 'El parqueadero seleccionado no está disponible.'
                ], 400);
            }

            // Crear la visita
            $visita = Visita::create([
                'copropiedad_id' => $propiedad->id,
                'unidad_id' => $validated['unidad_id'],
                'residente_id' => $validated['residente_id'] ?? null,
                'nombre_visitante' => $validated['nombre_visitante'],
                'documento_visitante' => $validated['documento_visitante'] ?? null,
                'tipo_visita' => $validated['tipo_visita'],
                'placa_vehiculo' => $validated['placa_vehiculo'] ?? null,
                'parqueadero_id' => $validated['parqueadero_id'],
                'motivo' => $validated['motivo'] ?? null,
                'fecha_ingreso' => Carbon::parse($validated['fecha_ingreso']),
                'estado' => 'activa',
                'registrada_por' => Auth::id(),
                'observaciones' => $validated['observaciones'] ?? null,
                'activo' => true,
            ]);

            // Actualizar estado del parqueadero a ocupado
            $parqueadero->update(['estado' => 'ocupado']);

            // Verificar si el cobro de parqueaderos está activo
            $cobroParqVisitantes = DB::table('configuraciones_propiedad')
                ->where('propiedad_id', $propiedad->id)
                ->where('clave', 'cobro_parq_visitantes')
                ->value('valor');

            if ($cobroParqVisitantes === 'true') {
                // Obtener minutos de gracia y valor por minuto
                $minutosGracia = DB::table('configuraciones_propiedad')
                    ->where('propiedad_id', $propiedad->id)
                    ->where('clave', 'minutos_gracia_parq_visitantes')
                    ->value('valor');

                $valorMinuto = DB::table('configuraciones_propiedad')
                    ->where('propiedad_id', $propiedad->id)
                    ->where('clave', 'valor_minuto_parq_visitantes')
                    ->value('valor');

                // Crear registro de liquidación
                LiquidacionParqueaderoVisitante::create([
                    'visita_id' => $visita->id,
                    'parqueadero_id' => $validated['parqueadero_id'],
                    'hora_llegada' => Carbon::parse($validated['fecha_ingreso']),
                    'minutos_gracia' => (int) ($minutosGracia ?? 0),
                    'valor_minuto' => (float) ($valorMinuto ?? 0),
                    'estado' => 'en_curso',
                    'activo' => true,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Visita registrada correctamente.',
                'visita' => $visita
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear visita desde parqueaderos visitantes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la visita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información de la visita activa de un parqueadero
     */
    public function getVisitaPorParqueadero($parqueaderoId)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No hay propiedad asignada.'
            ], 400);
        }

        // Verificar que el parqueadero pertenezca a la propiedad
        $parqueadero = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('id', $parqueaderoId)
            ->where('tipo', 'visitantes')
            ->where('activo', true)
            ->first();

        if (!$parqueadero) {
            return response()->json([
                'success' => false,
                'message' => 'Parqueadero no encontrado.'
            ], 404);
        }

        // Obtener la visita activa del parqueadero
        $visita = Visita::where('parqueadero_id', $parqueaderoId)
            ->where('estado', 'activa')
            ->where('activo', true)
            ->with(['unidad', 'residente.user', 'parqueadero'])
            ->first();

        if (!$visita) {
            return response()->json([
                'success' => false,
                'message' => 'No hay visita activa para este parqueadero.'
            ], 404);
        }

        // Obtener liquidación si existe
        $liquidacion = LiquidacionParqueaderoVisitante::where('visita_id', $visita->id)
            ->where('activo', true)
            ->first();

        // Verificar si el cobro está activo
        $cobroParqVisitantes = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $propiedad->id)
            ->where('clave', 'cobro_parq_visitantes')
            ->value('valor');

        return response()->json([
            'success' => true,
            'data' => [
                'visita' => $visita,
                'liquidacion' => $liquidacion,
                'cobro_activo' => $cobroParqVisitantes === 'true',
            ]
        ]);
    }

    /**
     * Finalizar pago - Paso 2: Crear/actualizar liquidación real
     */
    public function finalizarPago(Request $request, $visitaId)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No hay propiedad asignada.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Obtener la visita
            $visita = Visita::where('copropiedad_id', $propiedad->id)
                ->where('id', $visitaId)
                ->where('estado', 'activa')
                ->where('activo', true)
                ->first();

            if (!$visita) {
                return response()->json([
                    'success' => false,
                    'message' => 'Visita no encontrada o ya finalizada.'
                ], 404);
            }

            // Verificar que el cobro esté activo
            $cobroParqVisitantes = DB::table('configuraciones_propiedad')
                ->where('propiedad_id', $propiedad->id)
                ->where('clave', 'cobro_parq_visitantes')
                ->value('valor');

            if ($cobroParqVisitantes !== 'true') {
                return response()->json([
                    'success' => false,
                    'message' => 'El cobro de parqueaderos no está activo.'
                ], 400);
            }

            $fechaSalida = Carbon::now();

            // Obtener o crear liquidación
            $liquidacion = LiquidacionParqueaderoVisitante::where('visita_id', $visita->id)
                ->where('activo', true)
                ->first();

            if (!$liquidacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liquidación no encontrada.'
                ], 404);
            }

            // Calcular minutos totales (diferencia entre hora de salida y hora de llegada)
            $horaLlegada = Carbon::parse($liquidacion->hora_llegada);
            $minutosTotales = (int) $horaLlegada->diffInMinutes($fechaSalida);
            
            // Calcular minutos cobrados (restando minutos de gracia)
            $minutosCobrados = max(0, $minutosTotales - (int)$liquidacion->minutos_gracia);
            
            // Calcular valor total: minutos cobrados × valor por minuto
            // Asegurar que ambos valores sean numéricos y usar cálculo exacto
            $minutosCobradosInt = (int) $minutosCobrados;
            // Obtener valor_minuto directamente de la configuración para asegurar precisión
            $valorMinutoConfig = DB::table('configuraciones_propiedad')
                ->where('propiedad_id', $propiedad->id)
                ->where('clave', 'valor_minuto_parq_visitantes')
                ->value('valor');
            $valorMinutoFloat = (float) $valorMinutoConfig;
            
            // Calcular valor total exacto: minutos × valor_minuto
            // Usar cálculo directo sin redondeo intermedio
            $valorTotal = $minutosCobradosInt * $valorMinutoFloat;
            // Redondear solo el resultado final a 2 decimales
            $valorTotal = round($valorTotal, 2);

            // Actualizar liquidación (Paso 2: Liquidación real)
            $liquidacion->update([
                'hora_salida' => $fechaSalida,
                'minutos_totales' => $minutosTotales,
                'minutos_cobrados' => $minutosCobrados,
                'valor_total' => $valorTotal,
                'fecha_liquidacion' => Carbon::now()->toDateString(),
                'usuario_liquidador_id' => Auth::id(),
            ]);

            // NO actualizar la visita todavía, NO liberar parqueadero todavía
            // Eso se hace en el Paso 3 (recibir pago)

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Liquidación calculada correctamente.',
                'data' => [
                    'visita' => $visita->fresh(['unidad', 'residente.user', 'parqueadero']),
                    'liquidacion' => $liquidacion->fresh(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al finalizar pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular la liquidación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar una visita (solo cuando NO hay cobro activo)
     */
    public function finalizarVisita(Request $request, $visitaId)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No hay propiedad asignada.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Obtener la visita
            $visita = Visita::where('copropiedad_id', $propiedad->id)
                ->where('id', $visitaId)
                ->where('estado', 'activa')
                ->where('activo', true)
                ->first();

            if (!$visita) {
                return response()->json([
                    'success' => false,
                    'message' => 'Visita no encontrada o ya finalizada.'
                ], 404);
            }

            $fechaSalida = Carbon::now();

            // Verificar si el cobro está activo
            $cobroParqVisitantes = DB::table('configuraciones_propiedad')
                ->where('propiedad_id', $propiedad->id)
                ->where('clave', 'cobro_parq_visitantes')
                ->value('valor');

            // Este método solo se usa cuando NO hay cobro activo
            if ($cobroParqVisitantes === 'true') {
                return response()->json([
                    'success' => false,
                    'message' => 'Use el método finalizarPago cuando hay cobro activo.'
                ], 400);
            }

            // Si no hay cobro, actualizar la visita y liberar el parqueadero
            $visita->update([
                'fecha_salida' => $fechaSalida,
                'estado' => 'finalizada',
                'activo' => false,
            ]);
            
            // Liberar el parqueadero inmediatamente
            Parqueadero::where('id', $visita->parqueadero_id)
                ->update(['estado' => 'disponible']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Visita finalizada correctamente.',
                'data' => [
                    'visita' => $visita->fresh(['unidad', 'residente.user', 'parqueadero']),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al finalizar visita: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la visita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recibir pago de una liquidación
     */
    public function recibirPago(Request $request, $liquidacionId)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No hay propiedad asignada.'
            ], 400);
        }

        $validated = $request->validate([
            'metodo_pago' => 'required|in:efectivo,billetera_virtual',
        ], [
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
        ]);

        try {
            DB::beginTransaction();

            // Obtener la liquidación
            $liquidacion = LiquidacionParqueaderoVisitante::where('id', $liquidacionId)
                ->where('activo', true)
                ->where('estado', 'en_curso')
                ->with('visita')
                ->first();

            if (!$liquidacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liquidación no encontrada o ya pagada.'
                ], 404);
            }

            // Verificar que la visita pertenezca a la propiedad
            if ($liquidacion->visita->copropiedad_id != $propiedad->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'La liquidación no pertenece a la propiedad activa.'
                ], 403);
            }

            // Paso 3: Confirmación del pago
            // Actualizar liquidación
            $liquidacion->update([
                'metodo_pago' => $validated['metodo_pago'],
                'estado' => 'pagado',
                'activo' => false,
            ]);

            // Actualizar la visita con los datos de la liquidación
            $visita = $liquidacion->visita;
            if ($visita && $visita->estado === 'activa') {
                $visita->update([
                    'fecha_salida' => Carbon::now(), // Fecha actual
                    'estado' => 'finalizada',
                    'activo' => false,
                ]);
            }

            // Liberar parqueadero
            Parqueadero::where('id', $liquidacion->parqueadero_id)
                ->update(['estado' => 'disponible']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado correctamente.',
                'data' => [
                    'liquidacion' => $liquidacion->fresh(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al recibir pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
