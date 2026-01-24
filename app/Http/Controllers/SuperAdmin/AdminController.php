<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreAdminRequest;
use App\Http\Requests\SuperAdmin\UpdateAdminRequest;
use App\Models\User;
use App\Models\Role;
use App\Models\AdministradorPropiedad;
use App\Models\LogAuditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Listar todos los administradores
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('slug', 'administrador');
        })->with(['roles', 'administracionesPropiedad.propiedad']);

        // Filtros
        if ($request->has('propiedad_id')) {
            $query->whereHas('administracionesPropiedad', function ($q) use ($request) {
                $q->where('propiedad_id', $request->propiedad_id);
            });
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Paginación
        $perPage = $request->get('per_page', 15);
        $administradores = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $administradores,
            'message' => 'Administradores obtenidos exitosamente'
        ]);
    }

    /**
     * Crear un nuevo administrador
     *
     * @param StoreAdminRequest $request
     * @return JsonResponse
     */
    public function store(StoreAdminRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Crear usuario
            $user = User::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'documento_identidad' => $request->documento_identidad,
                'tipo_documento' => $request->tipo_documento,
                'activo' => true,
            ]);

            // Asignar rol de administrador
            $rolAdministrador = Role::where('slug', 'administrador')->first();
            $user->roles()->attach($rolAdministrador->id, [
                'propiedad_id' => $request->propiedad_id
            ]);

            // Crear registro en administradores_propiedad
            AdministradorPropiedad::create([
                'user_id' => $user->id,
                'propiedad_id' => $request->propiedad_id,
                'es_principal' => $request->boolean('es_principal', false),
                'fecha_inicio' => $request->fecha_inicio ?? now(),
                'fecha_fin' => $request->fecha_fin,
                'permisos_especiales' => $request->permisos_especiales,
            ]);

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'propiedad_id' => $request->propiedad_id,
                'accion' => 'create',
                'modelo' => 'User',
                'modelo_id' => $user->id,
                'descripcion' => "Administrador creado: {$user->nombre}",
                'datos_nuevos' => $user->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $user->load(['roles', 'administracionesPropiedad.propiedad']),
                'message' => 'Administrador creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el administrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un administrador específico
     *
     * @param User $administrador
     * @return JsonResponse
     */
    public function show(User $administrador): JsonResponse
    {
        $administrador->load(['roles', 'administracionesPropiedad.propiedad']);

        return response()->json([
            'success' => true,
            'data' => $administrador,
            'message' => 'Administrador obtenido exitosamente'
        ]);
    }

    /**
     * Actualizar un administrador
     *
     * @param UpdateAdminRequest $request
     * @param User $administrador
     * @return JsonResponse
     */
    public function update(UpdateAdminRequest $request, User $administrador): JsonResponse
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $administrador->toArray();
            
            $data = $request->validated();
            
            // Si se actualiza la contraseña, hashearla
            if ($request->has('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $administrador->update($data);

            // Actualizar relación con propiedad si se proporciona
            if ($request->has('propiedad_id')) {
                $administracion = $administrador->administracionesPropiedad()
                    ->where('propiedad_id', $request->propiedad_id)
                    ->first();

                if ($administracion) {
                    $administracion->update([
                        'es_principal' => $request->boolean('es_principal', $administracion->es_principal),
                        'fecha_inicio' => $request->fecha_inicio ?? $administracion->fecha_inicio,
                        'fecha_fin' => $request->fecha_fin ?? $administracion->fecha_fin,
                        'permisos_especiales' => $request->permisos_especiales ?? $administracion->permisos_especiales,
                    ]);
                }
            }

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'update',
                'modelo' => 'User',
                'modelo_id' => $administrador->id,
                'descripcion' => "Administrador actualizado: {$administrador->nombre}",
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $administrador->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $administrador->load(['roles', 'administracionesPropiedad.propiedad']),
                'message' => 'Administrador actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el administrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un administrador (soft delete)
     *
     * @param User $administrador
     * @return JsonResponse
     */
    public function destroy(User $administrador): JsonResponse
    {
        DB::beginTransaction();
        try {
            $datosAnteriores = $administrador->toArray();
            $administrador->delete();

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'delete',
                'modelo' => 'User',
                'modelo_id' => $administrador->id,
                'descripcion' => "Administrador eliminado: {$administrador->nombre}",
                'datos_anteriores' => $datosAnteriores,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'modulo' => 'SuperAdmin',
                'nivel' => 'warning',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Administrador eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el administrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
