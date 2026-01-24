<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePropiedadRequest;
use App\Http\Requests\SuperAdmin\UpdatePropiedadRequest;
use App\Models\Propiedad;
use App\Models\Role;
use App\Models\LogAuditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropiedadController extends Controller
{
    /**
     * Listar todas las propiedades con paginación
     *
     * @param Request $request
     * @return \Illuminate\View\View|JsonResponse
     */
    public function index(Request $request)
    {
        $query = Propiedad::with(['plan', 'suscripcionActiva']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('nit', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $propiedades = $query->paginate($perPage);

        // Registrar auditoría
        LogAuditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'list',
            'modelo' => 'Propiedad',
            'descripcion' => 'Listado de propiedades',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'modulo' => 'SuperAdmin',
        ]);

        // Si es una petición API, devolver JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $propiedades,
                'message' => 'Propiedades obtenidas exitosamente'
            ]);
        }

        // Si es web, devolver vista
        return view('superadmin.propiedades.index', compact('propiedades'));
    }

    /**
     * Crear una nueva propiedad
     *
     * @param StorePropiedadRequest $request
     * @return JsonResponse
     */
    public function store(StorePropiedadRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $propiedad = Propiedad::create($request->validated());

            // Asignar rol de administrador si se proporciona
            if ($request->has('admin_user_id')) {
                $propiedad->users()->attach($request->admin_user_id, [
                    'role_id' => Role::where('slug', 'administrador')->first()->id
                ]);
            }

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'propiedad_id' => $propiedad->id,
                'accion' => 'create',
                'modelo' => 'Propiedad',
                'modelo_id' => $propiedad->id,
                'descripcion' => "Propiedad creada: {$propiedad->nombre}",
                'datos_nuevos' => $propiedad->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $propiedad->load(['plan', 'suscripcionActiva']),
                'message' => 'Propiedad creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la propiedad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una propiedad específica
     *
     * @param Propiedad $propiedad
     * @return JsonResponse
     */
    public function show(Propiedad $propiedad): JsonResponse
    {
        $propiedad->load([
            'plan',
            'suscripcionActiva',
            'modulos',
            'administradores.user',
            'unidades',
            'configuraciones'
        ]);

        return response()->json([
            'success' => true,
            'data' => $propiedad,
            'message' => 'Propiedad obtenida exitosamente'
        ]);
    }

    /**
     * Actualizar una propiedad
     *
     * @param UpdatePropiedadRequest $request
     * @param Propiedad $propiedad
     * @return JsonResponse
     */
    public function update(UpdatePropiedadRequest $request, Propiedad $propiedad): JsonResponse
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $propiedad->toArray();
            $propiedad->update($request->validated());

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'propiedad_id' => $propiedad->id,
                'accion' => 'update',
                'modelo' => 'Propiedad',
                'modelo_id' => $propiedad->id,
                'descripcion' => "Propiedad actualizada: {$propiedad->nombre}",
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $propiedad->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $propiedad->load(['plan', 'suscripcionActiva']),
                'message' => 'Propiedad actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la propiedad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una propiedad (soft delete)
     *
     * @param Propiedad $propiedad
     * @return JsonResponse
     */
    public function destroy(Propiedad $propiedad): JsonResponse
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $propiedad->toArray();
            $propiedad->delete();

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'propiedad_id' => $propiedad->id,
                'accion' => 'delete',
                'modelo' => 'Propiedad',
                'modelo_id' => $propiedad->id,
                'descripcion' => "Propiedad eliminada: {$propiedad->nombre}",
                'datos_anteriores' => $datosAnteriores,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'modulo' => 'SuperAdmin',
                'nivel' => 'warning',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Propiedad eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la propiedad',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
