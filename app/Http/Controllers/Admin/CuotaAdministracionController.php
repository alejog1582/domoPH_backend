<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuotaAdministracion;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuotaAdministracionController extends Controller
{
    /**
     * Mostrar la lista de cuotas de administración
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = CuotaAdministracion::where('propiedad_id', $propiedad->id)
            ->where('activo', true);

        // Filtro por concepto
        if ($request->filled('concepto')) {
            $query->where('concepto', $request->concepto);
        }

        // Filtro por coeficiente
        if ($request->filled('coeficiente')) {
            $query->where('coeficiente', $request->coeficiente);
        }

        $cuotas = $query->orderBy('concepto')
            ->orderBy('coeficiente')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.cuotas-administracion.index', compact('cuotas', 'propiedad'));
    }

    /**
     * Mostrar el formulario de creación de una cuota
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener coeficientes únicos de las unidades para el select
        $coeficientes = DB::table('unidades')
            ->where('propiedad_id', $propiedad->id)
            ->whereNotNull('coeficiente')
            ->select('coeficiente')
            ->distinct()
            ->orderBy('coeficiente')
            ->pluck('coeficiente')
            ->map(function($coef) {
                return (float) $coef;
            })
            ->unique()
            ->values();

        return view('admin.cuotas-administracion.create', compact('propiedad', 'coeficientes'));
    }

    /**
     * Guardar una nueva cuota
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'concepto' => 'required|in:cuota_ordinaria,cuota_extraordinaria',
            'coeficiente' => 'nullable|numeric|min:0',
            'valor' => 'required|numeric|min:0',
            'mes_desde' => 'nullable|date',
            'mes_hasta' => 'nullable|date|after_or_equal:mes_desde',
            'activo' => 'boolean',
        ], [
            'concepto.required' => 'El concepto es obligatorio.',
            'concepto.in' => 'El concepto seleccionado no es válido.',
            'valor.required' => 'El valor es obligatorio.',
            'valor.numeric' => 'El valor debe ser un número.',
            'valor.min' => 'El valor debe ser mayor o igual a 0.',
            'mes_hasta.after_or_equal' => 'El mes hasta debe ser posterior o igual al mes desde.',
        ]);

        try {
            // Si es cuota ordinaria, el coeficiente es obligatorio
            if ($validated['concepto'] === CuotaAdministracion::CONCEPTO_CUOTA_ORDINARIA && empty($validated['coeficiente'])) {
                return back()->with('error', 'El coeficiente es obligatorio para cuotas ordinarias.')
                    ->withInput();
            }

            // Verificar si ya existe una cuota con el mismo concepto y coeficiente
            $existe = CuotaAdministracion::where('propiedad_id', $propiedad->id)
                ->where('concepto', $validated['concepto'])
                ->where('coeficiente', $validated['coeficiente'] ?? null)
                ->where('activo', true)
                ->first();

            if ($existe) {
                return back()->with('error', 'Ya existe una cuota activa con este concepto y coeficiente.')
                    ->withInput();
            }

            $cuota = CuotaAdministracion::create([
                'propiedad_id' => $propiedad->id,
                'concepto' => $validated['concepto'],
                'coeficiente' => $validated['coeficiente'] ?? null,
                'valor' => $validated['valor'],
                'mes_desde' => $validated['mes_desde'] ?? null,
                'mes_hasta' => $validated['mes_hasta'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]);

            return redirect()->route('admin.cuotas-administracion.index')
                ->with('success', 'Cuota de administración creada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear cuota de administración: ' . $e->getMessage());
            return back()->with('error', 'Error al crear la cuota: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario de edición de una cuota
     */
    public function edit(CuotaAdministracion $cuotaAdministracion)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la cuota pertenezca a la propiedad activa
        if ($cuotaAdministracion->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.cuotas-administracion.index')
                ->with('error', 'No tiene permisos para editar esta cuota.');
        }

        // Obtener coeficientes únicos de las unidades para el select
        $coeficientes = DB::table('unidades')
            ->where('propiedad_id', $propiedad->id)
            ->whereNotNull('coeficiente')
            ->select('coeficiente')
            ->distinct()
            ->orderBy('coeficiente')
            ->pluck('coeficiente')
            ->map(function($coef) {
                return (float) $coef;
            })
            ->unique()
            ->values();

        return view('admin.cuotas-administracion.edit', compact('cuotaAdministracion', 'propiedad', 'coeficientes'));
    }

    /**
     * Actualizar una cuota
     */
    public function update(Request $request, CuotaAdministracion $cuotaAdministracion)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la cuota pertenezca a la propiedad activa
        if ($cuotaAdministracion->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.cuotas-administracion.index')
                ->with('error', 'No tiene permisos para editar esta cuota.');
        }

        $validated = $request->validate([
            'valor' => 'required|numeric|min:0',
            'mes_desde' => 'nullable|date',
            'mes_hasta' => 'nullable|date|after_or_equal:mes_desde',
            'activo' => 'boolean',
        ], [
            'valor.required' => 'El valor es obligatorio.',
            'valor.numeric' => 'El valor debe ser un número.',
            'valor.min' => 'El valor debe ser mayor o igual a 0.',
            'mes_hasta.after_or_equal' => 'El mes hasta debe ser posterior o igual al mes desde.',
        ]);

        try {
            $cuotaAdministracion->update([
                'valor' => $validated['valor'],
                'mes_desde' => $validated['mes_desde'] ?? null,
                'mes_hasta' => $validated['mes_hasta'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]);

            return redirect()->route('admin.cuotas-administracion.index')
                ->with('success', 'Cuota de administración actualizada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar cuota de administración: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar la cuota: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar una cuota (soft delete)
     */
    public function destroy(CuotaAdministracion $cuotaAdministracion)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la cuota pertenezca a la propiedad activa
        if ($cuotaAdministracion->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.cuotas-administracion.index')
                ->with('error', 'No tiene permisos para eliminar esta cuota.');
        }

        try {
            $cuotaAdministracion->delete();

            return redirect()->route('admin.cuotas-administracion.index')
                ->with('success', 'Cuota de administración eliminada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar cuota de administración: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la cuota: ' . $e->getMessage());
        }
    }
}
