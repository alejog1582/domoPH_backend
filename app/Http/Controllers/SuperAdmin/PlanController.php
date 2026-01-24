<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePlanRequest;
use App\Http\Requests\SuperAdmin\UpdatePlanRequest;
use App\Models\Plan;
use App\Models\LogAuditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    /**
     * Listar todos los planes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Plan::with('modulos');

        // Filtros
        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $query->ordenados();

        $planes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $planes,
            'message' => 'Planes obtenidos exitosamente'
        ]);
    }

    /**
     * Crear un nuevo plan
     *
     * @param StorePlanRequest $request
     * @return JsonResponse
     */
    public function store(StorePlanRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $plan = Plan::create($request->validated());

            // Asociar módulos si se proporcionan
            if ($request->has('modulos')) {
                $modulos = [];
                foreach ($request->modulos as $moduloId) {
                    $modulos[$moduloId] = ['activo' => true];
                }
                $plan->modulos()->sync($modulos);
            }

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'create',
                'modelo' => 'Plan',
                'modelo_id' => $plan->id,
                'descripcion' => "Plan creado: {$plan->nombre}",
                'datos_nuevos' => $plan->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plan->load('modulos'),
                'message' => 'Plan creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un plan específico
     *
     * @param Plan $plan
     * @return JsonResponse
     */
    public function show(Plan $plan): JsonResponse
    {
        $plan->load('modulos');

        return response()->json([
            'success' => true,
            'data' => $plan,
            'message' => 'Plan obtenido exitosamente'
        ]);
    }

    /**
     * Actualizar un plan
     *
     * @param UpdatePlanRequest $request
     * @param Plan $plan
     * @return JsonResponse
     */
    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $plan->toArray();
            $plan->update($request->validated());

            // Actualizar módulos si se proporcionan
            if ($request->has('modulos')) {
                $modulos = [];
                foreach ($request->modulos as $moduloId) {
                    $modulos[$moduloId] = ['activo' => true];
                }
                $plan->modulos()->sync($modulos);
            }

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'update',
                'modelo' => 'Plan',
                'modelo_id' => $plan->id,
                'descripcion' => "Plan actualizado: {$plan->nombre}",
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $plan->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plan->load('modulos'),
                'message' => 'Plan actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un plan (soft delete)
     *
     * @param Plan $plan
     * @return JsonResponse
     */
    public function destroy(Plan $plan): JsonResponse
    {
        // Verificar que no tenga propiedades asociadas
        if ($plan->propiedades()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el plan porque tiene propiedades asociadas'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $datosAnteriores = $plan->toArray();
            $plan->delete();

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'delete',
                'modelo' => 'Plan',
                'modelo_id' => $plan->id,
                'descripcion' => "Plan eliminado: {$plan->nombre}",
                'datos_anteriores' => $datosAnteriores,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'modulo' => 'SuperAdmin',
                'nivel' => 'warning',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Plan eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
