<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePropiedadRequest;
use App\Http\Requests\SuperAdmin\UpdatePropiedadRequest;
use App\Models\Propiedad;
use App\Models\Role;
use App\Models\Modulo;
use App\Models\Plan;
use App\Models\User;
use App\Models\AdministradorPropiedad;
use App\Models\LogAuditoria;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
     * Mostrar formulario de creación de propiedad
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $planes = Plan::activos()->ordenados()->get();
        $modulos = Modulo::activos()->where('es_admin', true)->ordenados()->get();
        
        return view('superadmin.propiedades.create', compact('planes', 'modulos'));
    }

    /**
     * Crear una nueva propiedad
     *
     * @param StorePropiedadRequest $request
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function store(StorePropiedadRequest $request)
    {
        DB::beginTransaction();
        try {
            // Calcular fecha de fin de trial (último día del mes siguiente desde la fecha de creación)
            // Ejemplo: Si se crea el 15 de enero, el trial termina el 28/29 de febrero (último día del mes siguiente)
            $fechaCreacion = Carbon::now();
            $fechaFinTrial = $fechaCreacion->copy()->addMonth()->endOfMonth();

            // Preparar datos de la propiedad
            $datosPropiedad = $request->only([
                'nombre', 'nit', 'direccion', 'ciudad', 'departamento', 
                'codigo_postal', 'telefono', 'email', 'logo', 
                'color_primario', 'color_secundario', 'descripcion', 
                'total_unidades', 'estado', 'plan_id'
            ]);
            
            $datosPropiedad['trial_activo'] = true;
            $datosPropiedad['fecha_inicio_suscripcion'] = $fechaCreacion;
            $datosPropiedad['fecha_fin_trial'] = $fechaFinTrial;

            // Crear la propiedad
            $propiedad = Propiedad::create($datosPropiedad);

            // Crear usuario administrador
            $adminUser = User::create([
                'nombre' => $request->admin_nombre,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'telefono' => $request->admin_telefono,
                'documento_identidad' => $request->admin_documento_identidad,
                'tipo_documento' => $request->admin_tipo_documento,
                'activo' => true,
            ]);

            // Crear registro en administradores_propiedad
            AdministradorPropiedad::create([
                'user_id' => $adminUser->id,
                'propiedad_id' => $propiedad->id,
                'fecha_inicio' => $fechaCreacion,
                'es_principal' => true,
            ]);

            // Asociar módulos seleccionados
            $modulosIds = [];
            if ($request->has('modulos') && is_array($request->modulos)) {
                $modulosData = [];
                foreach ($request->modulos as $moduloId) {
                    $modulosData[$moduloId] = [
                        'activo' => true,
                        'fecha_activacion' => $fechaCreacion,
                    ];
                }
                $propiedad->modulos()->attach($modulosData);
                $modulosIds = $request->modulos;
            }

            // Crear rol específico para esta propiedad y asignar permisos
            $rolPropiedad = $this->crearRolYAsignarPermisos($propiedad, $modulosIds);

            // Asignar el rol específico al usuario administrador
            $adminUser->roles()->attach($rolPropiedad->id, [
                'propiedad_id' => $propiedad->id
            ]);

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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $propiedad->load(['plan', 'suscripcionActiva', 'modulos', 'administradores']),
                    'message' => 'Propiedad creada exitosamente'
                ], 201);
            }

            return redirect()->route('superadmin.propiedades.index')
                ->with('success', 'Propiedad creada exitosamente. El administrador ha sido creado automáticamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la propiedad',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Error al crear la propiedad: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición de propiedad
     *
     * @param Propiedad $propiedad
     * @return \Illuminate\View\View
     */
    public function edit(Propiedad $propiedad)
    {
        $propiedad->load(['plan', 'modulos', 'administradores.user']);
        $planes = Plan::activos()->ordenados()->get();
        $modulos = Modulo::activos()->where('es_admin', true)->ordenados()->get();
        $administradorPrincipal = $propiedad->administradores->where('es_principal', true)->first();
        
        return view('superadmin.propiedades.edit', compact('propiedad', 'planes', 'modulos', 'administradorPrincipal'));
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
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function update(UpdatePropiedadRequest $request, Propiedad $propiedad)
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $propiedad->toArray();
            
            // Actualizar datos de la propiedad
            $datosPropiedad = $request->only([
                'nombre', 'nit', 'direccion', 'ciudad', 'departamento', 
                'codigo_postal', 'telefono', 'email', 'logo', 
                'color_primario', 'color_secundario', 'descripcion', 
                'total_unidades', 'estado', 'plan_id'
            ]);
            
            $propiedad->update($datosPropiedad);

            // Actualizar módulos si se enviaron
            $modulosIds = [];
            if ($request->has('modulos') && is_array($request->modulos)) {
                $modulosData = [];
                foreach ($request->modulos as $moduloId) {
                    $modulosData[$moduloId] = [
                        'activo' => true,
                        'fecha_activacion' => Carbon::now(),
                    ];
                }
                $propiedad->modulos()->sync($modulosData);
                $modulosIds = $request->modulos;
            } else {
                // Si no se enviaron módulos, obtener los módulos actuales de la propiedad
                $modulosIds = $propiedad->modulos()->pluck('modulos.id')->toArray();
            }

            // Actualizar o crear rol específico para esta propiedad y asignar permisos
            $rolPropiedad = $this->crearRolYAsignarPermisos($propiedad, $modulosIds);

            // Actualizar el rol del administrador principal si existe
            $administradorPrincipal = $propiedad->administradores()->where('es_principal', true)->first();
            if ($administradorPrincipal && $administradorPrincipal->user) {
                // Eliminar roles anteriores del usuario para esta propiedad
                DB::table('role_user')
                    ->where('user_id', $administradorPrincipal->user_id)
                    ->where('propiedad_id', $propiedad->id)
                    ->delete();

                // Asignar el rol específico al usuario administrador
                DB::table('role_user')->insert([
                    'user_id' => $administradorPrincipal->user_id,
                    'role_id' => $rolPropiedad->id,
                    'propiedad_id' => $propiedad->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

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

            // Si es una petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $propiedad->load(['plan', 'suscripcionActiva']),
                    'message' => 'Propiedad actualizada exitosamente'
                ]);
            }

            // Si es web, redirigir
            return redirect()->route('superadmin.propiedades.index')
                ->with('success', 'Propiedad actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si es una petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la propiedad',
                    'error' => $e->getMessage()
                ], 500);
            }

            // Si es web, redirigir con error
            return back()->withInput()
                ->with('error', 'Error al actualizar la propiedad: ' . $e->getMessage());
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

    /**
     * Crear o actualizar rol específico de la propiedad y asignar permisos
     *
     * @param Propiedad $propiedad
     * @param array $modulosIds
     * @return Role
     */
    private function crearRolYAsignarPermisos(Propiedad $propiedad, array $modulosIds): Role
    {
        // Generar nombre y slug del rol
        $nombreRol = "Administrador {$propiedad->nombre}";
        $slugRol = 'administrador_' . \Illuminate\Support\Str::slug($propiedad->nombre, '_');

        // Crear o actualizar el rol específico de la propiedad
        $rolPropiedad = Role::updateOrCreate(
            ['slug' => $slugRol],
            [
                'nombre' => $nombreRol,
                'descripcion' => "Rol de administrador específico para la propiedad {$propiedad->nombre}",
                'activo' => true,
            ]
        );

        // Si hay módulos asignados, obtener sus permisos
        if (!empty($modulosIds)) {
            // Obtener los slugs de los módulos asignados
            $modulosAsignados = Modulo::whereIn('id', $modulosIds)->pluck('slug')->toArray();
            
            // Obtener los permisos cuyo campo modulo coincida con los slugs de los módulos asignados
            $permisos = Permission::whereIn('modulo', $modulosAsignados)->pluck('id')->toArray();
            
            // Asignar permisos al rol específico de la propiedad
            $rolPropiedad->permissions()->sync($permisos);
        } else {
            // Si no hay módulos, eliminar todos los permisos del rol
            $rolPropiedad->permissions()->sync([]);
        }

        return $rolPropiedad;
    }
}
