<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visita;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VisitaController extends Controller
{
    /**
     * Mostrar la lista de visitas
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $mesActual = Carbon::now()->format('Y-m');
        
        // Query base: visitas con sus relaciones
        $query = Visita::with(['unidad', 'residente', 'registradoPor'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('activo', true);

        // Filtro por fecha (por defecto: mes actual)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_ingreso', '>=', $request->fecha_desde);
        } else {
            // Por defecto: mes actual
            $query->whereYear('fecha_ingreso', Carbon::now()->year)
                  ->whereMonth('fecha_ingreso', Carbon::now()->month);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_ingreso', '<=', $request->fecha_hasta);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por tipo de visita
        if ($request->filled('tipo_visita')) {
            $query->where('tipo_visita', $request->tipo_visita);
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

        // Filtro por nombre de visitante
        if ($request->filled('nombre_visitante')) {
            $query->where('nombre_visitante', 'like', "%{$request->nombre_visitante}%");
        }

        // Ordenar por fecha de ingreso descendente
        $visitas = $query->orderBy('fecha_ingreso', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener unidades para el filtro
        $unidades = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        return view('admin.visitas.index', compact(
            'visitas', 
            'propiedad', 
            'unidades',
            'mesActual'
        ));
    }

    /**
     * Mostrar el formulario de creación de una visita
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

        return view('admin.visitas.create', compact('propiedad', 'unidades'));
    }

    /**
     * Guardar una nueva visita
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'unidad_id' => 'required|exists:unidades,id',
            'residente_id' => 'nullable|exists:residentes,id',
            'nombre_visitante' => 'required|string|max:150',
            'documento_visitante' => 'nullable|string|max:50',
            'tipo_visita' => 'required|in:peatonal,vehicular',
            'placa_vehiculo' => 'nullable|string|max:20|required_if:tipo_visita,vehicular',
            'motivo' => 'nullable|string|max:200',
            'fecha_ingreso' => 'required|date',
            'observaciones' => 'nullable|string',
        ], [
            'unidad_id.required' => 'La unidad es obligatoria.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'nombre_visitante.required' => 'El nombre del visitante es obligatorio.',
            'tipo_visita.required' => 'El tipo de visita es obligatorio.',
            'tipo_visita.in' => 'El tipo de visita seleccionado no es válido.',
            'placa_vehiculo.required_if' => 'La placa del vehículo es obligatoria para visitas vehiculares.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
        ]);

        try {
            // Verificar que la unidad pertenezca a la propiedad activa
            $unidad = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
                ->where('id', $validated['unidad_id'])
                ->first();

            if (!$unidad) {
                return back()->with('error', 'La unidad no pertenece a la propiedad activa.')
                    ->withInput();
            }

            $visita = Visita::create([
                'copropiedad_id' => $propiedad->id,
                'unidad_id' => $validated['unidad_id'],
                'residente_id' => $validated['residente_id'] ?? null,
                'nombre_visitante' => $validated['nombre_visitante'],
                'documento_visitante' => $validated['documento_visitante'] ?? null,
                'tipo_visita' => $validated['tipo_visita'],
                'placa_vehiculo' => $validated['placa_vehiculo'] ?? null,
                'motivo' => $validated['motivo'] ?? null,
                'fecha_ingreso' => Carbon::parse($validated['fecha_ingreso']),
                'estado' => 'activa',
                'registrada_por' => Auth::id(),
                'observaciones' => $validated['observaciones'] ?? null,
                'activo' => true,
            ]);

            return redirect()->route('admin.visitas.index')
                ->with('success', 'Visita registrada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear visita: ' . $e->getMessage());
            return back()->with('error', 'Error al registrar la visita: ' . $e->getMessage())
                ->withInput();
        }
    }
}
