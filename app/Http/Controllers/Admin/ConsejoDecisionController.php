<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsejoDecisionController extends Controller
{
    /**
     * Display a listing of decisiones (most recent first).
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = DB::table('consejo_decisiones')
            ->join('consejo_actas', 'consejo_decisiones.acta_id', '=', 'consejo_actas.id')
            ->where('consejo_actas.copropiedad_id', $propiedad->id)
            ->select('consejo_decisiones.*', 'consejo_actas.fecha_acta', 'consejo_actas.tipo_reunion')
            ->orderBy('consejo_actas.fecha_acta', 'desc')
            ->orderBy('consejo_decisiones.created_at', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('consejo_decisiones.estado', $request->estado);
        }

        if ($request->filled('acta_id')) {
            $query->where('consejo_decisiones.acta_id', $request->acta_id);
        }

        $decisiones = $query->paginate(15)->appends($request->query());

        // Verificar si cada decisión puede editarse/eliminarse
        foreach ($decisiones as $decision) {
            $firmasCount = DB::table('consejo_acta_firmas')
                ->where('acta_id', $decision->acta_id)
                ->count();
            $decision->puede_editar = $firmasCount == 0;
            $decision->puede_eliminar = $firmasCount == 0;
        }

        // Obtener actas para filtro
        $actas = DB::table('consejo_actas')
            ->where('copropiedad_id', $propiedad->id)
            ->orderBy('fecha_acta', 'desc')
            ->get();

        return view('admin.consejo-decisiones.index', compact('decisiones', 'actas', 'propiedad'));
    }

    /**
     * Show the form for creating a new decision.
     */
    public function create(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener actas de los últimos 3 meses
        $actas = DB::table('consejo_actas')
            ->where('copropiedad_id', $propiedad->id)
            ->where('fecha_acta', '>=', Carbon::now()->subMonths(3))
            ->orderBy('fecha_acta', 'desc')
            ->get();

        // Si viene de un acta específica
        $actaId = $request->get('acta_id');

        return view('admin.consejo-decisiones.create', compact('actas', 'actaId', 'propiedad'));
    }

    /**
     * Store a newly created decision.
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'acta_id' => 'required|exists:consejo_actas,id',
            'descripcion' => 'required|string',
            'responsable' => 'nullable|string|max:255',
            'fecha_compromiso' => 'nullable|date',
        ]);

        // Verificar que el acta pertenece a la propiedad
        $acta = DB::table('consejo_actas')
            ->where('id', $validated['acta_id'])
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$acta) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Acta no encontrada.');
        }

        // Verificar si el acta tiene firmas
        $firmasCount = DB::table('consejo_acta_firmas')
            ->where('acta_id', $validated['acta_id'])
            ->count();

        if ($firmasCount > 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No se pueden crear decisiones en un acta que ya tiene firmas.');
        }

        DB::beginTransaction();
        try {
            DB::table('consejo_decisiones')->insert([
                'acta_id' => $validated['acta_id'],
                'descripcion' => $validated['descripcion'],
                'responsable' => $validated['responsable'] ?? null,
                'fecha_compromiso' => $validated['fecha_compromiso'] ?? null,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.consejo-decisiones.index')
                ->with('success', 'Decisión creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear decisión: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la decisión.');
        }
    }

    /**
     * Display the specified decision.
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $decision = DB::table('consejo_decisiones')
            ->join('consejo_actas', 'consejo_decisiones.acta_id', '=', 'consejo_actas.id')
            ->where('consejo_decisiones.id', $id)
            ->where('consejo_actas.copropiedad_id', $propiedad->id)
            ->select('consejo_decisiones.*', 'consejo_actas.fecha_acta', 'consejo_actas.tipo_reunion')
            ->first();

        if (!$decision) {
            return redirect()->route('admin.consejo-decisiones.index')
                ->with('error', 'Decisión no encontrada.');
        }

        // Verificar si puede editar/eliminar
        $firmasCount = DB::table('consejo_acta_firmas')
            ->where('acta_id', $decision->acta_id)
            ->count();
        $puede_editar = $firmasCount == 0;
        $puede_eliminar = $firmasCount == 0;

        // Obtener tareas asociadas
        $tareas = DB::table('consejo_tareas')
            ->where('decision_id', $id)
            ->get();

        return view('admin.consejo-decisiones.show', compact('decision', 'tareas', 'puede_editar', 'puede_eliminar', 'propiedad'));
    }

    /**
     * Show the form for editing the specified decision.
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $decision = DB::table('consejo_decisiones')
            ->join('consejo_actas', 'consejo_decisiones.acta_id', '=', 'consejo_actas.id')
            ->where('consejo_decisiones.id', $id)
            ->where('consejo_actas.copropiedad_id', $propiedad->id)
            ->select('consejo_decisiones.*')
            ->first();

        if (!$decision) {
            return redirect()->route('admin.consejo-decisiones.index')
                ->with('error', 'Decisión no encontrada.');
        }

        // Verificar si el acta tiene firmas
        $firmasCount = DB::table('consejo_acta_firmas')
            ->where('acta_id', $decision->acta_id)
            ->count();

        if ($firmasCount > 0) {
            return redirect()->route('admin.consejo-decisiones.index')
                ->with('error', 'No se puede editar una decisión cuyo acta ya tiene firmas.');
        }

        return view('admin.consejo-decisiones.edit', compact('decision', 'propiedad'));
    }

    /**
     * Update the specified decision.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $decision = DB::table('consejo_decisiones')
            ->join('consejo_actas', 'consejo_decisiones.acta_id', '=', 'consejo_actas.id')
            ->where('consejo_decisiones.id', $id)
            ->where('consejo_actas.copropiedad_id', $propiedad->id)
            ->select('consejo_decisiones.*')
            ->first();

        if (!$decision) {
            return redirect()->route('admin.consejo-decisiones.index')
                ->with('error', 'Decisión no encontrada.');
        }

        // Verificar si el acta tiene firmas
        $firmasCount = DB::table('consejo_acta_firmas')
            ->where('acta_id', $decision->acta_id)
            ->count();

        if ($firmasCount > 0) {
            return redirect()->route('admin.consejo-decisiones.index')
                ->with('error', 'No se puede editar una decisión cuyo acta ya tiene firmas.');
        }

        // Verificar si el usuario es presidente (solo presidente puede cambiar estado)
        $integrante = DB::table('consejo_integrantes')
            ->where('user_id', auth()->id())
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->first();

        $esPresidente = $integrante && $integrante->es_presidente == true;

        $validated = $request->validate([
            'descripcion' => 'required|string',
            'responsable' => 'nullable|string|max:255',
            'fecha_compromiso' => 'nullable|date',
            'estado' => 'required|in:pendiente,en_proceso,cumplida',
        ]);

        // Solo presidente puede cambiar estado
        if (!$esPresidente && $validated['estado'] !== $decision->estado) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Solo el presidente puede cambiar el estado de las decisiones.');
        }

        DB::beginTransaction();
        try {
            DB::table('consejo_decisiones')
                ->where('id', $id)
                ->update([
                    'descripcion' => $validated['descripcion'],
                    'responsable' => $validated['responsable'] ?? null,
                    'fecha_compromiso' => $validated['fecha_compromiso'] ?? null,
                    'estado' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()->route('admin.consejo-decisiones.index')
                ->with('success', 'Decisión actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar decisión: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la decisión.');
        }
    }

    /**
     * Remove the specified decision.
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $decision = DB::table('consejo_decisiones')
            ->join('consejo_actas', 'consejo_decisiones.acta_id', '=', 'consejo_actas.id')
            ->where('consejo_decisiones.id', $id)
            ->where('consejo_actas.copropiedad_id', $propiedad->id)
            ->select('consejo_decisiones.*')
            ->first();

        if (!$decision) {
            return redirect()->route('admin.consejo-decisiones.index')
                ->with('error', 'Decisión no encontrada.');
        }

        // Verificar si el acta tiene firmas
        $firmasCount = DB::table('consejo_acta_firmas')
            ->where('acta_id', $decision->acta_id)
            ->count();

        if ($firmasCount > 0) {
            return redirect()->route('admin.consejo-decisiones.index')
                ->with('error', 'No se puede eliminar una decisión cuyo acta ya tiene firmas.');
        }

        DB::beginTransaction();
        try {
            DB::table('consejo_decisiones')->where('id', $id)->delete();

            DB::commit();

            return redirect()->route('admin.consejo-decisiones.index')
                ->with('success', 'Decisión eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar decisión: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar la decisión.');
        }
    }
}
