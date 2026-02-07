<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsejoReunionController extends Controller
{
    /**
     * Display a listing of reuniones (most recent first).
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = DB::table('consejo_reuniones')
            ->where('copropiedad_id', $propiedad->id)
            ->orderBy('fecha_inicio', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo_reunion')) {
            $query->where('tipo_reunion', $request->tipo_reunion);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_hasta);
        }

        $reuniones = $query->paginate(15)->appends($request->query());

        // Obtener actas asociadas para cada reunión
        foreach ($reuniones as $reunion) {
            $reunion->tiene_acta = DB::table('consejo_actas')
                ->where('reunion_id', $reunion->id)
                ->exists();
        }

        return view('admin.consejo-reuniones.index', compact('reuniones', 'propiedad'));
    }

    /**
     * Show the form for creating a new reunion.
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener integrantes activos
        $integrantes = DB::table('consejo_integrantes')
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('admin.consejo-reuniones.create', compact('integrantes', 'propiedad'));
    }

    /**
     * Store a newly created reunion.
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo_reunion' => 'required|in:ordinaria,extraordinaria',
            'modalidad' => 'required|in:presencial,virtual,mixta',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'lugar' => 'nullable|string|max:255',
            'enlace_virtual' => 'nullable|url|max:500',
            'observaciones' => 'nullable|string',
            'agenda' => 'nullable|array',
            'agenda.*.tema' => 'required_with:agenda|string|max:255',
            'agenda.*.responsable' => 'nullable|string|max:255',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'exists:consejo_integrantes,id',
        ]);

        DB::beginTransaction();
        try {
            // Crear reunión
            $reunionId = DB::table('consejo_reuniones')->insertGetId([
                'copropiedad_id' => $propiedad->id,
                'titulo' => $validated['titulo'],
                'tipo_reunion' => $validated['tipo_reunion'],
                'modalidad' => $validated['modalidad'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'lugar' => $validated['lugar'] ?? null,
                'enlace_virtual' => $validated['enlace_virtual'] ?? null,
                'estado' => 'programada',
                'observaciones' => $validated['observaciones'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear agenda
            if (!empty($validated['agenda'])) {
                $orden = 1;
                foreach ($validated['agenda'] as $item) {
                    DB::table('consejo_reunion_agenda')->insert([
                        'reunion_id' => $reunionId,
                        'orden' => $orden++,
                        'tema' => $item['tema'],
                        'responsable' => $item['responsable'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Crear asistencias (por defecto todos marcados como no asistieron)
            if (!empty($validated['integrantes'])) {
                foreach ($validated['integrantes'] as $integranteId) {
                    DB::table('consejo_reunion_asistencias')->insert([
                        'reunion_id' => $reunionId,
                        'integrante_id' => $integranteId,
                        'asistio' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.consejo-reuniones.index')
                ->with('success', 'Reunión creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear reunión: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la reunión.');
        }
    }

    /**
     * Display the specified reunion.
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $reunion = DB::table('consejo_reuniones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$reunion) {
            return redirect()->route('admin.consejo-reuniones.index')
                ->with('error', 'Reunión no encontrada.');
        }

        // Obtener agenda
        $agenda = DB::table('consejo_reunion_agenda')
            ->where('reunion_id', $id)
            ->orderBy('orden')
            ->get();

        // Obtener asistencias
        $asistencias = DB::table('consejo_reunion_asistencias')
            ->join('consejo_integrantes', 'consejo_reunion_asistencias.integrante_id', '=', 'consejo_integrantes.id')
            ->where('consejo_reunion_asistencias.reunion_id', $id)
            ->select('consejo_reunion_asistencias.*', 'consejo_integrantes.nombre', 'consejo_integrantes.cargo')
            ->get();

        // Verificar si tiene acta
        $acta = DB::table('consejo_actas')
            ->where('reunion_id', $id)
            ->first();

        return view('admin.consejo-reuniones.show', compact('reunion', 'agenda', 'asistencias', 'acta', 'propiedad'));
    }

    /**
     * Show the form for editing the specified reunion.
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $reunion = DB::table('consejo_reuniones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$reunion) {
            return redirect()->route('admin.consejo-reuniones.index')
                ->with('error', 'Reunión no encontrada.');
        }

        // Solo se pueden editar reuniones en estado programada
        if ($reunion->estado !== 'programada') {
            return redirect()->route('admin.consejo-reuniones.index')
                ->with('error', 'Solo se pueden editar reuniones en estado programada.');
        }

        // Obtener agenda
        $agenda = DB::table('consejo_reunion_agenda')
            ->where('reunion_id', $id)
            ->orderBy('orden')
            ->get();

        // Obtener integrantes activos
        $integrantes = DB::table('consejo_integrantes')
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        // Obtener asistencias
        $asistencias = DB::table('consejo_reunion_asistencias')
            ->where('reunion_id', $id)
            ->pluck('integrante_id')
            ->toArray();

        return view('admin.consejo-reuniones.edit', compact('reunion', 'agenda', 'integrantes', 'asistencias', 'propiedad'));
    }

    /**
     * Update the specified reunion.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $reunion = DB::table('consejo_reuniones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$reunion) {
            return redirect()->route('admin.consejo-reuniones.index')
                ->with('error', 'Reunión no encontrada.');
        }

        // Solo se pueden editar reuniones en estado programada
        if ($reunion->estado !== 'programada') {
            return redirect()->route('admin.consejo-reuniones.index')
                ->with('error', 'Solo se pueden editar reuniones en estado programada.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo_reunion' => 'required|in:ordinaria,extraordinaria',
            'modalidad' => 'required|in:presencial,virtual,mixta',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'lugar' => 'nullable|string|max:255',
            'enlace_virtual' => 'nullable|url|max:500',
            'observaciones' => 'nullable|string',
            'agenda' => 'nullable|array',
            'agenda.*.tema' => 'required_with:agenda|string|max:255',
            'agenda.*.responsable' => 'nullable|string|max:255',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'exists:consejo_integrantes,id',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar reunión
            DB::table('consejo_reuniones')
                ->where('id', $id)
                ->update([
                    'titulo' => $validated['titulo'],
                    'tipo_reunion' => $validated['tipo_reunion'],
                    'modalidad' => $validated['modalidad'],
                    'fecha_inicio' => $validated['fecha_inicio'],
                    'fecha_fin' => $validated['fecha_fin'] ?? null,
                    'lugar' => $validated['lugar'] ?? null,
                    'enlace_virtual' => $validated['enlace_virtual'] ?? null,
                    'observaciones' => $validated['observaciones'] ?? null,
                    'updated_at' => now(),
                ]);

            // Eliminar agenda anterior y crear nueva
            DB::table('consejo_reunion_agenda')->where('reunion_id', $id)->delete();
            if (!empty($validated['agenda'])) {
                $orden = 1;
                foreach ($validated['agenda'] as $item) {
                    DB::table('consejo_reunion_agenda')->insert([
                        'reunion_id' => $id,
                        'orden' => $orden++,
                        'tema' => $item['tema'],
                        'responsable' => $item['responsable'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Actualizar asistencias
            DB::table('consejo_reunion_asistencias')->where('reunion_id', $id)->delete();
            if (!empty($validated['integrantes'])) {
                foreach ($validated['integrantes'] as $integranteId) {
                    DB::table('consejo_reunion_asistencias')->insert([
                        'reunion_id' => $id,
                        'integrante_id' => $integranteId,
                        'asistio' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.consejo-reuniones.index')
                ->with('success', 'Reunión actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar reunión: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la reunión.');
        }
    }
}
