<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateConfiguracionRequest;
use App\Models\ConfiguracionGlobal;
use App\Models\LogAuditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    /**
     * Obtener todas las configuraciones globales
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $configuraciones = ConfiguracionGlobal::orderBy('categoria')
            ->orderBy('clave')
            ->get()
            ->groupBy('categoria');

        return response()->json([
            'success' => true,
            'data' => $configuraciones,
            'message' => 'Configuraciones obtenidas exitosamente'
        ]);
    }

    /**
     * Actualizar configuraciones globales
     *
     * @param UpdateConfiguracionRequest $request
     * @return JsonResponse
     */
    public function update(UpdateConfiguracionRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = [];
            $datosNuevos = [];

            foreach ($request->configuraciones as $config) {
                $configuracion = ConfiguracionGlobal::where('clave', $config['clave'])->first();

                if ($configuracion && $configuracion->editable) {
                    $datosAnteriores[$config['clave']] = $configuracion->valor;
                    $configuracion->valor = $config['valor'];
                    $configuracion->save();
                    $datosNuevos[$config['clave']] = $configuracion->valor;
                }
            }

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'update',
                'modelo' => 'ConfiguracionGlobal',
                'descripcion' => 'Configuraciones globales actualizadas',
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $datosNuevos,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones actualizadas exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las configuraciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
