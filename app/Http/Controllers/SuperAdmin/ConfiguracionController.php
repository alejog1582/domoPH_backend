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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|JsonResponse
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $configuraciones = ConfiguracionGlobal::orderBy('categoria')
            ->orderBy('clave')
            ->get()
            ->groupBy('categoria');

        // Si es una petición API, devolver JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $configuraciones,
                'message' => 'Configuraciones obtenidas exitosamente'
            ]);
        }

        // Si es web, devolver vista
        return view('superadmin.configuraciones.index', compact('configuraciones'));
    }

    /**
     * Actualizar configuraciones globales
     *
     * @param UpdateConfiguracionRequest $request
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function update(UpdateConfiguracionRequest $request)
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

            // Si es una petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuraciones actualizadas exitosamente'
                ]);
            }

            // Si es web, redirigir con mensaje de éxito
            return redirect()->route('superadmin.configuraciones.index')
                ->with('success', 'Configuraciones actualizadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si es una petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar las configuraciones',
                    'error' => $e->getMessage()
                ], 500);
            }

            // Si es web, redirigir con mensaje de error
            return redirect()->route('superadmin.configuraciones.index')
                ->with('error', 'Error al actualizar las configuraciones: ' . $e->getMessage());
        }
    }
}
