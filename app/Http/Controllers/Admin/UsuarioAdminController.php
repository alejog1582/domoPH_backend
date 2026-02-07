<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use App\Models\User;
use App\Models\Role;
use App\Models\Modulo;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener usuarios asociados a la propiedad_id
        $propiedadIds = explode(',', $propiedad->id);
        
        $query = User::where(function($q) use ($propiedadIds) {
            foreach ($propiedadIds as $propId) {
                $q->orWhere('propiedad_id', 'like', "%{$propId}%");
            }
        })
        ->where('perfil', '!=', 'administrador') // Excluir solo administradores
        ->where('perfil', '!=', 'superadministrador') // Excluir también superadministradores
        ->with(['roles']);

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('documento_identidad', 'like', "%{$buscar}%");
            });
        }

        // Filtro por perfil
        if ($request->filled('perfil')) {
            $query->where('perfil', $request->perfil);
        }

        // Filtro por activo
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo == '1');
        }

        $usuarios = $query->orderBy('nombre', 'asc')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.usuarios-admin.index', compact('usuarios', 'propiedad'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener módulos con es_admin = true
        $modulos = Modulo::activos()
            ->where('es_admin', true)
            ->ordenados()
            ->get();

        return view('admin.usuarios-admin.create', compact('modulos', 'propiedad'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'required|string|max:20',
            'documento_identidad' => 'required|string|max:20',
            'tipo_documento' => 'required|in:CC,CE,PA,NIT',
            'perfil' => 'required|in:residente,porteria,proveedor,comite_convivencia,consejo_administracion',
            'modulos' => 'required|array|min:1',
            'modulos.*' => 'exists:modulos,id',
            'activo' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Crear usuario
            $user = User::create([
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'telefono' => $validated['telefono'] ?? null,
                'documento_identidad' => $validated['documento_identidad'] ?? null,
                'tipo_documento' => $validated['tipo_documento'] ?? null,
                'perfil' => $validated['perfil'],
                'activo' => $request->has('activo') ? true : false,
                'propiedad_id' => (string) $propiedad->id,
            ]);

            // Crear rol dinámico: [perfil_users] [nombre]
            $nombreRol = Str::ucfirst($validated['perfil']) . ' ' . $validated['nombre'];
            $slugRol = Str::slug($validated['perfil'] . '_' . $validated['nombre'], '_');
            
            $rol = Role::create([
                'nombre' => $nombreRol,
                'slug' => $slugRol,
                'descripcion' => "Rol personalizado para {$validated['nombre']}",
                'activo' => true,
            ]);

            // Obtener permisos de los módulos seleccionados
            $modulosSeleccionados = Modulo::whereIn('id', $validated['modulos'])->pluck('slug')->toArray();
            $permisos = Permission::whereIn('modulo', $modulosSeleccionados)->pluck('id')->toArray();
            
            // Asignar permisos al rol
            $rol->permissions()->sync($permisos);

            // Asignar rol al usuario
            $user->roles()->attach($rol->id, [
                'propiedad_id' => $propiedad->id
            ]);

            DB::commit();

            return redirect()->route('admin.usuarios-admin.index')
                ->with('success', 'Usuario administrador creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $usuarioAdmin)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que el usuario pertenece a la propiedad
        $propiedadIds = explode(',', $propiedad->id);
        $usuarioPropiedadIds = $usuarioAdmin->propiedad_id ? explode(',', $usuarioAdmin->propiedad_id) : [];
        
        if (!array_intersect($propiedadIds, $usuarioPropiedadIds)) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        // Obtener módulos con es_admin = true
        $modulos = Modulo::activos()
            ->where('es_admin', true)
            ->ordenados()
            ->get();

        // Obtener módulos asignados al usuario (a través de sus roles y permisos)
        $usuarioAdmin->load(['roles.permissions']);
        $modulosAsignados = [];
        foreach ($usuarioAdmin->roles as $rol) {
            foreach ($rol->permissions as $permiso) {
                $modulo = Modulo::where('slug', $permiso->modulo)->first();
                if ($modulo && $modulo->es_admin) {
                    $modulosAsignados[] = $modulo->id;
                }
            }
        }
        $modulosAsignados = array_unique($modulosAsignados);

        return view('admin.usuarios-admin.edit', compact('usuarioAdmin', 'modulos', 'modulosAsignados', 'propiedad'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $usuarioAdmin)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que el usuario pertenece a la propiedad
        $propiedadIds = explode(',', $propiedad->id);
        $usuarioPropiedadIds = $usuarioAdmin->propiedad_id ? explode(',', $usuarioAdmin->propiedad_id) : [];
        
        if (!array_intersect($propiedadIds, $usuarioPropiedadIds)) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuarioAdmin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'documento_identidad' => 'nullable|string|max:20',
            'tipo_documento' => 'nullable|in:CC,CE,PA,NIT',
            'perfil' => 'required|in:residente,porteria,proveedor,comite_convivencia,consejo_administracion',
            'modulos' => 'required|array|min:1',
            'modulos.*' => 'exists:modulos,id',
            'activo' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar usuario
            $updateData = [
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
                'documento_identidad' => $validated['documento_identidad'] ?? null,
                'tipo_documento' => $validated['tipo_documento'] ?? null,
                'perfil' => $validated['perfil'],
                'activo' => $request->has('activo') ? true : false,
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $usuarioAdmin->update($updateData);

            // Actualizar o crear rol dinámico
            $nombreRol = Str::ucfirst($validated['perfil']) . ' ' . $validated['nombre'];
            $slugRol = Str::slug($validated['perfil'] . '_' . $validated['nombre'], '_');
            
            // Buscar rol existente del usuario
            $rolExistente = $usuarioAdmin->roles()->where('slug', 'like', $validated['perfil'] . '_%')->first();
            
            if ($rolExistente) {
                // Actualizar rol existente
                $rolExistente->update([
                    'nombre' => $nombreRol,
                    'slug' => $slugRol,
                ]);
                $rol = $rolExistente;
            } else {
                // Crear nuevo rol
                $rol = Role::create([
                    'nombre' => $nombreRol,
                    'slug' => $slugRol,
                    'descripcion' => "Rol personalizado para {$validated['nombre']}",
                    'activo' => true,
                ]);
                
                // Asignar rol al usuario
                $usuarioAdmin->roles()->attach($rol->id, [
                    'propiedad_id' => $propiedad->id
                ]);
            }

            // Obtener permisos de los módulos seleccionados
            $modulosSeleccionados = Modulo::whereIn('id', $validated['modulos'])->pluck('slug')->toArray();
            $permisos = Permission::whereIn('modulo', $modulosSeleccionados)->pluck('id')->toArray();
            
            // Actualizar permisos del rol
            $rol->permissions()->sync($permisos);

            DB::commit();

            return redirect()->route('admin.usuarios-admin.index')
                ->with('success', 'Usuario administrador actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuarioAdmin)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No hay propiedad asignada.'
            ], 400);
        }

        // Verificar que el usuario pertenece a la propiedad
        $propiedadIds = explode(',', $propiedad->id);
        $usuarioPropiedadIds = $usuarioAdmin->propiedad_id ? explode(',', $usuarioAdmin->propiedad_id) : [];
        
        if (!array_intersect($propiedadIds, $usuarioPropiedadIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este usuario.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Eliminar roles personalizados del usuario
            $usuarioAdmin->roles()->where('slug', 'like', $usuarioAdmin->perfil . '_%')->delete();
            
            // Eliminar usuario (soft delete)
            $usuarioAdmin->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }
}
