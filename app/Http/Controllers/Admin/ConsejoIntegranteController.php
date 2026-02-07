<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use App\Models\Propiedad;
use App\Models\User;
use App\Models\Role;
use App\Models\Modulo;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ConsejoIntegranteController extends Controller
{
    /**
     * Display a listing of integrantes (only active).
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = DB::table('consejo_integrantes')
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->orderBy('nombre', 'asc');

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('cargo', 'like', "%{$buscar}%");
            });
        }

        // Filtro por cargo
        if ($request->filled('cargo')) {
            $query->where('cargo', $request->cargo);
        }

        $integrantes = $query->paginate(15)->appends($request->query());

        return view('admin.consejo-integrantes.index', compact('integrantes', 'propiedad'));
    }

    /**
     * Show the form for creating a new integrante.
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        try {
            // Obtener módulos con es_consejo = true
            $modulos = Modulo::where('activo', true)
                ->where('es_consejo', true)
                ->orderBy('orden')
                ->get();

            return view('admin.consejo-integrantes.create', compact('modulos', 'propiedad'));
        } catch (\Exception $e) {
            \Log::error('Error en create de integrante: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('admin.consejo-integrantes.index')
                ->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created integrante.
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
            'telefono' => 'nullable|string|max:20',
            'unidad_apartamento' => 'nullable|string|max:50',
            'cargo' => 'required|string|max:100',
            'es_presidente' => 'nullable|boolean',
            'tiene_voz' => 'nullable|boolean',
            'tiene_voto' => 'nullable|boolean',
            'puede_convocar' => 'nullable|boolean',
            'puede_firmar_actas' => 'nullable|boolean',
            'fecha_inicio_periodo' => 'required|date',
            'fecha_fin_periodo' => 'nullable|date|after:fecha_inicio_periodo',
            'modulos' => 'required|array|min:1',
            'modulos.*' => 'exists:modulos,id',
        ]);

        // Normalizar valores booleanos (checkboxes no marcados no se envían)
        $validated['es_presidente'] = $request->has('es_presidente') ? (bool)$request->es_presidente : false;
        $validated['tiene_voz'] = $request->has('tiene_voz') ? (bool)$request->tiene_voz : true;
        $validated['tiene_voto'] = $request->has('tiene_voto') ? (bool)$request->tiene_voto : true;
        $validated['puede_convocar'] = $request->has('puede_convocar') ? (bool)$request->puede_convocar : false;
        $validated['puede_firmar_actas'] = $request->has('puede_firmar_actas') ? (bool)$request->puede_firmar_actas : false;

        // Validar que solo puede haber un presidente activo
        if ($validated['es_presidente']) {
            $presidenteExistente = DB::table('consejo_integrantes')
                ->where('copropiedad_id', $propiedad->id)
                ->where('es_presidente', true)
                ->where('estado', 'activo')
                ->exists();

            if ($presidenteExistente) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe un presidente activo en el consejo. Solo puede haber uno.');
            }
        }

        DB::beginTransaction();
        try {
            // Crear usuario
            $user = User::create([
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'password' => Hash::make('12345678'), // Password por defecto
                'telefono' => $validated['telefono'] ?? null,
                'documento_identidad' => '100000000' . rand(1, 9),
                'tipo_documento' => 'CC', // CC = Cédula de Ciudadanía
                'activo' => true,
                'perfil' => 'consejo_administracion',
                'propiedad_id' => (string) $propiedad->id,
            ]);

            // Crear rol específico para el integrante
            $nombreRol = "Consejo {$validated['nombre']}";
            $slugRol = 'consejo_' . Str::slug($validated['nombre'], '_');

            $rolIntegrante = Role::updateOrCreate(
                ['slug' => $slugRol],
                [
                    'nombre' => $nombreRol,
                    'descripcion' => "Rol específico para el integrante del consejo {$validated['nombre']}",
                    'activo' => true,
                ]
            );

            // Obtener permisos de los módulos seleccionados
            $modulosSeleccionados = Modulo::whereIn('id', $validated['modulos'])
                ->where('es_consejo', true)
                ->pluck('slug')
                ->toArray();

            $permisos = Permission::whereIn('modulo', $modulosSeleccionados)
                ->pluck('id')
                ->toArray();

            // Asignar permisos al rol
            $rolIntegrante->permissions()->sync($permisos);

            // Asociar usuario con el rol
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $rolIntegrante->id,
                'propiedad_id' => $propiedad->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear registro en consejo_integrantes
            DB::table('consejo_integrantes')->insert([
                'copropiedad_id' => $propiedad->id,
                'user_id' => $user->id,
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
                'unidad_apartamento' => $validated['unidad_apartamento'] ?? null,
                'cargo' => $validated['cargo'],
                'es_presidente' => $validated['es_presidente'],
                'tiene_voz' => $validated['tiene_voz'],
                'tiene_voto' => $validated['tiene_voto'],
                'puede_convocar' => $validated['puede_convocar'],
                'puede_firmar_actas' => $validated['puede_firmar_actas'],
                'fecha_inicio_periodo' => $validated['fecha_inicio_periodo'],
                'fecha_fin_periodo' => $validated['fecha_fin_periodo'] ?? null,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.consejo-integrantes.index')
                ->with('success', 'Integrante del consejo creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear integrante del consejo: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el integrante del consejo: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified integrante.
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $integrante = DB::table('consejo_integrantes')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$integrante) {
            return redirect()->route('admin.consejo-integrantes.index')
                ->with('error', 'Integrante no encontrado.');
        }

        // Obtener usuario asociado
        $user = $integrante->user_id ? User::find($integrante->user_id) : null;

        return view('admin.consejo-integrantes.show', compact('integrante', 'user', 'propiedad'));
    }

    /**
     * Show the form for editing the specified integrante.
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $integrante = DB::table('consejo_integrantes')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$integrante) {
            return redirect()->route('admin.consejo-integrantes.index')
                ->with('error', 'Integrante no encontrado.');
        }

        // Obtener módulos con es_consejo = true
        $modulos = Modulo::activos()
            ->where('es_consejo', true)
            ->ordenados()
            ->get();

        // Obtener módulos asignados al usuario (si existe)
        $modulosAsignados = [];
        if ($integrante->user_id) {
            $user = User::find($integrante->user_id);
            if ($user) {
                // Obtener solo el rol que contiene "consejo" en el slug
                $rolConsejo = $user->roles()
                    ->where('slug', 'like', 'consejo_%')
                    ->first();
                
                if ($rolConsejo) {
                    // Obtener permisos del rol del consejo
                    $permisos = Permission::whereHas('roles', function($q) use ($rolConsejo) {
                        $q->where('roles.id', $rolConsejo->id);
                    })->pluck('modulo')->unique()->toArray();
                    
                    // Obtener módulos del consejo asignados
                    $modulosAsignados = Modulo::whereIn('slug', $permisos)
                        ->where('es_consejo', true)
                        ->pluck('id')
                        ->toArray();
                }
            }
        }

        return view('admin.consejo-integrantes.edit', compact('integrante', 'modulos', 'modulosAsignados', 'propiedad'));
    }

    /**
     * Update the specified integrante.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $integrante = DB::table('consejo_integrantes')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$integrante) {
            return redirect()->route('admin.consejo-integrantes.index')
                ->with('error', 'Integrante no encontrado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($integrante->user_id ?? 'NULL'),
            'telefono' => 'nullable|string|max:20',
            'unidad_apartamento' => 'nullable|string|max:50',
            'cargo' => 'required|string|max:100',
            'es_presidente' => 'boolean',
            'tiene_voz' => 'boolean',
            'tiene_voto' => 'boolean',
            'puede_convocar' => 'boolean',
            'puede_firmar_actas' => 'boolean',
            'fecha_inicio_periodo' => 'required|date',
            'fecha_fin_periodo' => 'nullable|date|after:fecha_inicio_periodo',
            'estado' => 'required|in:activo,inactivo',
            'modulos' => 'required|array|min:1',
            'modulos.*' => 'exists:modulos,id',
        ]);

        // Validar que solo puede haber un presidente activo
        if ($validated['es_presidente'] ?? false && $validated['estado'] === 'activo') {
            $presidenteExistente = DB::table('consejo_integrantes')
                ->where('copropiedad_id', $propiedad->id)
                ->where('es_presidente', true)
                ->where('estado', 'activo')
                ->where('id', '!=', $id)
                ->exists();

            if ($presidenteExistente) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe un presidente activo en el consejo. Solo puede haber uno.');
            }
        }

        DB::beginTransaction();
        try {
            // Actualizar usuario si existe
            if ($integrante->user_id) {
                $user = User::find($integrante->user_id);
                if ($user) {
                    $user->update([
                        'nombre' => $validated['nombre'],
                        'email' => $validated['email'],
                        'telefono' => $validated['telefono'] ?? null,
                    ]);

                    // Actualizar rol y permisos
                    $nombreRol = "Consejo {$validated['nombre']}";
                    $slugRol = 'consejo_' . Str::slug($validated['nombre'], '_');

                    $rolIntegrante = Role::updateOrCreate(
                        ['slug' => $slugRol],
                        [
                            'nombre' => $nombreRol,
                            'descripcion' => "Rol específico para el integrante del consejo {$validated['nombre']}",
                            'activo' => true,
                        ]
                    );

                    // Obtener permisos de los módulos seleccionados
                    $modulosSeleccionados = Modulo::whereIn('id', $validated['modulos'])
                        ->where('es_consejo', true)
                        ->pluck('slug')
                        ->toArray();

                    $permisos = Permission::whereIn('modulo', $modulosSeleccionados)
                        ->pluck('id')
                        ->toArray();

                    // Asignar permisos al rol
                    $rolIntegrante->permissions()->sync($permisos);

                    // Actualizar asociación usuario-rol
                    DB::table('role_user')
                        ->where('user_id', $user->id)
                        ->where('propiedad_id', $propiedad->id)
                        ->update([
                            'role_id' => $rolIntegrante->id,
                            'updated_at' => now(),
                        ]);
                }
            }

            // Actualizar registro en consejo_integrantes
            DB::table('consejo_integrantes')
                ->where('id', $id)
                ->update([
                    'nombre' => $validated['nombre'],
                    'email' => $validated['email'],
                    'telefono' => $validated['telefono'] ?? null,
                    'unidad_apartamento' => $validated['unidad_apartamento'] ?? null,
                    'cargo' => $validated['cargo'],
                    'es_presidente' => $validated['es_presidente'] ?? false,
                    'tiene_voz' => $validated['tiene_voz'] ?? true,
                    'tiene_voto' => $validated['tiene_voto'] ?? true,
                    'puede_convocar' => $validated['puede_convocar'] ?? false,
                    'puede_firmar_actas' => $validated['puede_firmar_actas'] ?? false,
                    'fecha_inicio_periodo' => $validated['fecha_inicio_periodo'],
                    'fecha_fin_periodo' => $validated['fecha_fin_periodo'] ?? null,
                    'estado' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()->route('admin.consejo-integrantes.index')
                ->with('success', 'Integrante del consejo actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar integrante del consejo: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el integrante del consejo.');
        }
    }
}
