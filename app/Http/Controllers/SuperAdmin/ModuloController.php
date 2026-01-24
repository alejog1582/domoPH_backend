<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreModuloRequest;
use App\Http\Requests\SuperAdmin\UpdateModuloRequest;
use App\Models\Modulo;
use App\Models\LogAuditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuloController extends Controller
{
    /**
     * Listar todos los módulos
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Modulo::query();

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

        $modulos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $modulos,
            'message' => 'Módulos obtenidos exitosamente'
        ]);
    }

    /**
     * Crear un nuevo módulo
     *
     * @param StoreModuloRequest $request
     * @return JsonResponse
     */
    public function store(StoreModuloRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $modulo = Modulo::create($request->validated());

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'create',
                'modelo' => 'Modulo',
                'modelo_id' => $modulo->id,
                'descripcion' => "Módulo creado: {$modulo->nombre}",
                'datos_nuevos' => $modulo->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $modulo,
                'message' => 'Módulo creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el módulo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un módulo
     *
     * @param UpdateModuloRequest $request
     * @param Modulo $modulo
     * @return JsonResponse
     */
    public function update(UpdateModuloRequest $request, Modulo $modulo): JsonResponse
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $modulo->toArray();
            $modulo->update($request->validated());

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'update',
                'modelo' => 'Modulo',
                'modelo_id' => $modulo->id,
                'descripcion' => "Módulo actualizado: {$modulo->nombre}",
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $modulo->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $modulo,
                'message' => 'Módulo actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el módulo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
