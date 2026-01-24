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
     * @return \Illuminate\View\View|JsonResponse
     */
    public function index(Request $request)
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

        // Si es una petición API, devolver JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $planes,
                'message' => 'Planes obtenidos exitosamente'
            ]);
        }

        // Si es web, devolver vista
        return view('superadmin.planes.index', compact('planes'));
    }

    /**
     * Crear un nuevo plan
     *
     * @param StorePlanRequest $request
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function store(StorePlanRequest $request)
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

            // Si es una petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $plan->load('modulos'),
                    'message' => 'Plan creado exitosamente'
                ], 201);
            }

            // Si es web, redirigir con mensaje de éxito
            return redirect()->route('superadmin.planes.index')
                ->with('success', 'Plan creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si es una petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el plan',
                    'error' => $e->getMessage()
                ], 500);
            }

            // Si es web, redirigir con mensaje de error
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el plan: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $modulos = \App\Models\Modulo::activos()->ordenados()->get();
        return view('superadmin.planes.create', compact('modulos'));
    }

    /**
     * Obtener un plan específico
     *
     * @param Plan $plan
     * @return \Illuminate\View\View|JsonResponse
     */
    public function show(Plan $plan)
    {
        $plan->load('modulos');

        // Si es una petición API, devolver JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Plan obtenido exitosamente'
            ]);
        }

        // Si es web, redirigir a editar
        return redirect()->route('superadmin.planes.edit', $plan);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Plan $plan)
    {
        $plan->load('modulos');
        $modulos = \App\Models\Modulo::activos()->ordenados()->get();
        $modulosSeleccionados = $plan->modulos->pluck('id')->toArray();
        
        return view('superadmin.planes.edit', compact('plan', 'modulos', 'modulosSeleccionados'));
    }

    /**
     * Actualizar un plan
     *
     * @param UpdatePlanRequest $request
     * @param Plan $plan
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
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

            // Si es una petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $plan->load('modulos'),
                    'message' => 'Plan actualizado exitosamente'
                ]);
            }

            // Si es web, redirigir con mensaje de éxito
            return redirect()->route('superadmin.planes.index')
                ->with('success', 'Plan actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si es una petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el plan',
                    'error' => $e->getMessage()
                ], 500);
            }

            // Si es web, redirigir con mensaje de error
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el plan: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un plan (soft delete)
     *
     * @param Plan $plan
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function destroy(Plan $plan)
    {
        // Verificar que no tenga propiedades asociadas
        if ($plan->propiedades()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el plan porque tiene propiedades asociadas'
                ], 422);
            }
            
            return redirect()->route('superadmin.planes.index')
                ->with('error', 'No se puede eliminar el plan porque tiene propiedades asociadas');
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

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Plan eliminado exitosamente'
                ]);
            }

            return redirect()->route('superadmin.planes.index')
                ->with('success', 'Plan eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el plan',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->route('superadmin.planes.index')
                ->with('error', 'Error al eliminar el plan: ' . $e->getMessage());
        }
    }
}
