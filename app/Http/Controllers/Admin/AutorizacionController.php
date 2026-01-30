<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Autorizacion;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AutorizacionController extends Controller
{
    /**
     * Mostrar la lista de autorizaciones
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Query base: autorizaciones con sus relaciones
        $query = Autorizacion::with(['unidad', 'residente'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('activo', true);

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por tipo de autorizado
        if ($request->filled('tipo_autorizado')) {
            $query->where('tipo_autorizado', $request->tipo_autorizado);
        }

        // Filtro por tipo de acceso
        if ($request->filled('tipo_acceso')) {
            $query->where('tipo_acceso', $request->tipo_acceso);
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        // Filtro por búsqueda de unidad
        if ($request->filled('buscar_unidad')) {
            $buscar = $request->buscar_unidad;
            $query->whereHas('unidad', function($q) use ($buscar) {
                $q->where('numero', 'like', "%{$buscar}%")
                  ->orWhere('torre', 'like', "%{$buscar}%")
                  ->orWhere('bloque', 'like', "%{$buscar}%");
            });
        }

        // Filtro por nombre de autorizado
        if ($request->filled('nombre_autorizado')) {
            $query->where('nombre_autorizado', 'like', "%{$request->nombre_autorizado}%");
        }

        // Ordenar por fecha de creación descendente
        $autorizaciones = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener unidades para el filtro
        $unidades = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        return view('admin.autorizaciones.index', compact(
            'autorizaciones', 
            'propiedad', 
            'unidades'
        ));
    }

    /**
     * Mostrar el formulario de creación de una autorización
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener unidades para el select
        $unidades = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        return view('admin.autorizaciones.create', compact('propiedad', 'unidades'));
    }

    /**
     * Guardar una nueva autorización
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'unidad_id' => 'nullable|exists:unidades,id',
            'residente_id' => 'nullable|exists:residentes,id',
            'nombre_autorizado' => 'required|string|max:150',
            'documento_autorizado' => 'nullable|string|max:50',
            'tipo_autorizado' => 'required|in:familiar,empleado,aseo,mantenimiento,proveedor,otro',
            'tipo_acceso' => 'required|in:peatonal,vehicular,ambos',
            'placa_vehiculo' => 'nullable|string|max:20|required_if:tipo_acceso,vehicular|required_if:tipo_acceso,ambos',
            'dias_autorizados' => 'nullable|array',
            'dias_autorizados.*' => 'string|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_desde' => 'nullable|date_format:H:i',
            'hora_hasta' => 'nullable|date_format:H:i|after_or_equal:hora_desde',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string',
        ], [
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'nombre_autorizado.required' => 'El nombre del autorizado es obligatorio.',
            'tipo_autorizado.required' => 'El tipo de autorizado es obligatorio.',
            'tipo_autorizado.in' => 'El tipo de autorizado seleccionado no es válido.',
            'tipo_acceso.required' => 'El tipo de acceso es obligatorio.',
            'tipo_acceso.in' => 'El tipo de acceso seleccionado no es válido.',
            'placa_vehiculo.required_if' => 'La placa del vehículo es obligatoria para acceso vehicular o ambos.',
            'hora_hasta.after_or_equal' => 'La hora hasta debe ser posterior o igual a la hora desde.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ]);

        try {
            // Verificar que la unidad pertenezca a la propiedad activa (si se proporciona)
            if ($validated['unidad_id']) {
                $unidad = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
                    ->where('id', $validated['unidad_id'])
                    ->first();

                if (!$unidad) {
                    return back()->with('error', 'La unidad no pertenece a la propiedad activa.')
                        ->withInput();
                }
            }

            // Determinar estado inicial
            $estado = 'activa';
            if ($validated['fecha_fin'] && Carbon::parse($validated['fecha_fin']) < Carbon::now()) {
                $estado = 'vencida';
            }

            $autorizacion = Autorizacion::create([
                'copropiedad_id' => $propiedad->id,
                'unidad_id' => $validated['unidad_id'] ?? null,
                'residente_id' => $validated['residente_id'] ?? null,
                'nombre_autorizado' => $validated['nombre_autorizado'],
                'documento_autorizado' => $validated['documento_autorizado'] ?? null,
                'tipo_autorizado' => $validated['tipo_autorizado'],
                'tipo_acceso' => $validated['tipo_acceso'],
                'placa_vehiculo' => $validated['placa_vehiculo'] ?? null,
                'dias_autorizados' => $validated['dias_autorizados'] ?? null,
                'hora_desde' => $validated['hora_desde'] ?? null,
                'hora_hasta' => $validated['hora_hasta'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'] ? Carbon::parse($validated['fecha_inicio']) : null,
                'fecha_fin' => $validated['fecha_fin'] ? Carbon::parse($validated['fecha_fin']) : null,
                'estado' => $estado,
                'observaciones' => $validated['observaciones'] ?? null,
                'activo' => true,
            ]);

            return redirect()->route('admin.autorizaciones.index')
                ->with('success', 'Autorización creada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear autorización: ' . $e->getMessage());
            return back()->with('error', 'Error al crear la autorización: ' . $e->getMessage())
                ->withInput();
        }
    }
}
