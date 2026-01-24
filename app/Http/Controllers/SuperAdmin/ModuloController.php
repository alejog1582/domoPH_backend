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
     * @return \Illuminate\View\View|JsonResponse
     */
    public function index(Request $request)
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

        // Registrar auditoría
        LogAuditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'list',
            'modelo' => 'Modulo',
            'descripcion' => 'Listado de módulos',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'modulo' => 'SuperAdmin',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $modulos,
                'message' => 'Módulos obtenidos exitosamente'
            ]);
        }

        return view('superadmin.modulos.index', compact('modulos'));
    }

    /**
     * Mostrar formulario de creación de módulo
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('superadmin.modulos.create');
    }

    /**
     * Mostrar formulario de edición de módulo
     *
     * @param Modulo $modulo
     * @return \Illuminate\View\View
     */
    public function edit(Modulo $modulo)
    {
        return view('superadmin.modulos.edit', compact('modulo'));
    }

    /**
     * Crear un nuevo módulo
     *
     * @param StoreModuloRequest $request
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function store(StoreModuloRequest $request)
    {
        DB::beginTransaction();
        try {
            $datos = $request->validated();
            
            // Procesar configuracion_default si viene como string JSON
            if (isset($datos['configuracion_default']) && is_string($datos['configuracion_default'])) {
                $datos['configuracion_default'] = json_decode($datos['configuracion_default'], true) ?? [];
            }
            
            $modulo = Modulo::create($datos);

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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $modulo,
                    'message' => 'Módulo creado exitosamente'
                ], 201);
            }

            return redirect()->route('superadmin.modulos.index')
                ->with('success', 'Módulo creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el módulo',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Error al crear el módulo: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar un módulo
     *
     * @param UpdateModuloRequest $request
     * @param Modulo $modulo
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function update(UpdateModuloRequest $request, Modulo $modulo)
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $modulo->toArray();
            $datos = $request->validated();
            
            // Procesar configuracion_default si viene como string JSON
            if (isset($datos['configuracion_default']) && is_string($datos['configuracion_default'])) {
                $datos['configuracion_default'] = json_decode($datos['configuracion_default'], true) ?? [];
            }
            
            $modulo->update($datos);

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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $modulo,
                    'message' => 'Módulo actualizado exitosamente'
                ]);
            }

            return redirect()->route('superadmin.modulos.index')
                ->with('success', 'Módulo actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el módulo',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Error al actualizar el módulo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un módulo (soft delete)
     *
     * @param Modulo $modulo
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function destroy(Modulo $modulo)
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $modulo->toArray();
            $modulo->delete();

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'delete',
                'modelo' => 'Modulo',
                'modelo_id' => $modulo->id,
                'descripcion' => "Módulo eliminado: {$modulo->nombre}",
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
                    'message' => 'Módulo eliminado exitosamente'
                ]);
            }

            return redirect()->route('superadmin.modulos.index')
                ->with('success', 'Módulo eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el módulo',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error al eliminar el módulo: ' . $e->getMessage());
        }
    }
}
