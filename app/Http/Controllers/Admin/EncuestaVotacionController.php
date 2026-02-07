<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use App\Models\Encuesta;
use App\Models\Votacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncuestaVotacionController extends Controller
{
    /**
     * Display a listing of encuestas and votaciones.
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tipo = $request->get('tipo', 'todos'); // 'encuestas', 'votaciones', 'todos'

        // Query para encuestas
        $queryEncuestas = Encuesta::where('copropiedad_id', $propiedad->id);
        
        // Query para votaciones
        $queryVotaciones = Votacion::where('copropiedad_id', $propiedad->id);

        // Filtro por estado
        if ($request->filled('estado')) {
            $queryEncuestas->where('estado', $request->estado);
            $queryVotaciones->where('estado', $request->estado);
        }

        // Filtro por búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $queryEncuestas->where(function($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
            $queryVotaciones->where(function($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $encuestas = collect([]);
        $votaciones = collect([]);

        if ($tipo === 'encuestas' || $tipo === 'todos') {
            $encuestas = $queryEncuestas->withCount('respuestas')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($tipo === 'votaciones' || $tipo === 'todos') {
            $votaciones = $queryVotaciones->withCount('votos')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.encuestas-votaciones.index', compact('encuestas', 'votaciones', 'propiedad', 'tipo'));
    }

    /**
     * Show the form for creating a new encuesta or votacion.
     */
    public function create(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tipo = $request->get('tipo', 'encuesta'); // 'encuesta' o 'votacion'

        return view('admin.encuestas-votaciones.create', compact('propiedad', 'tipo'));
    }

    /**
     * Store a newly created encuesta or votacion.
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tipo = $request->get('tipo', 'encuesta');

        if ($tipo === 'encuesta') {
            return $this->storeEncuesta($request, $propiedad);
        } else {
            return $this->storeVotacion($request, $propiedad);
        }
    }

    /**
     * Store a new encuesta.
     */
    private function storeEncuesta(Request $request, $propiedad)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estado' => 'required|in:activa,cerrada,anulada',
        ]);

        DB::beginTransaction();
        try {
            $encuesta = Encuesta::create([
                'copropiedad_id' => $propiedad->id,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo_respuesta' => 'respuesta_abierta', // Todas las encuestas son de respuesta abierta
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'estado' => $validated['estado'],
                'activo' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.encuestas-votaciones.index', ['tipo' => 'encuestas'])
                ->with('success', 'Encuesta creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al crear la encuesta: ' . $e->getMessage());
        }
    }

    /**
     * Store a new votacion.
     */
    private function storeVotacion(Request $request, $propiedad)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estado' => 'required|in:activa,cerrada,anulada',
            'opciones' => 'required|array|min:2',
            'opciones.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $votacion = Votacion::create([
                'copropiedad_id' => $propiedad->id,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'estado' => $validated['estado'],
                'activo' => true,
            ]);

            // Crear las opciones de votación
            foreach ($validated['opciones'] as $index => $textoOpcion) {
                \App\Models\VotacionOpcion::create([
                    'votacion_id' => $votacion->id,
                    'texto_opcion' => $textoOpcion,
                    'orden' => $index + 1,
                    'activo' => true,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.encuestas-votaciones.index', ['tipo' => 'votaciones'])
                ->with('success', 'Votación creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al crear la votación: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified encuesta or votacion.
     */
    public function show($id, Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tipo = $request->get('tipo', 'encuesta');

        if ($tipo === 'encuesta') {
            $encuesta = Encuesta::with(['opciones', 'respuestas.residente.user', 'respuestas.opcion'])
                ->where('copropiedad_id', $propiedad->id)
                ->findOrFail($id);
            
            return view('admin.encuestas-votaciones.show-encuesta', compact('encuesta', 'propiedad'));
        } else {
            $votacion = Votacion::with(['opciones', 'votos.residente.user', 'votos.opcion'])
                ->where('copropiedad_id', $propiedad->id)
                ->findOrFail($id);
            
            return view('admin.encuestas-votaciones.show-votacion', compact('votacion', 'propiedad'));
        }
    }

    /**
     * Show the form for editing the specified encuesta or votacion.
     */
    public function edit($id, Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tipo = $request->get('tipo', 'encuesta');

        if ($tipo === 'encuesta') {
            $encuesta = Encuesta::with('opciones')
                ->where('copropiedad_id', $propiedad->id)
                ->findOrFail($id);
            
            return view('admin.encuestas-votaciones.edit-encuesta', compact('encuesta', 'propiedad'));
        } else {
            $votacion = Votacion::with('opciones')
                ->where('copropiedad_id', $propiedad->id)
                ->findOrFail($id);
            
            return view('admin.encuestas-votaciones.edit-votacion', compact('votacion', 'propiedad'));
        }
    }

    /**
     * Update the specified encuesta or votacion.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tipo = $request->get('tipo', 'encuesta');

        if ($tipo === 'encuesta') {
            return $this->updateEncuesta($request, $id, $propiedad);
        } else {
            return $this->updateVotacion($request, $id, $propiedad);
        }
    }

    /**
     * Update an encuesta.
     */
    private function updateEncuesta(Request $request, $id, $propiedad)
    {
        $encuesta = Encuesta::where('copropiedad_id', $propiedad->id)->findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estado' => 'required|in:activa,cerrada,anulada',
        ]);

        DB::beginTransaction();
        try {
            $encuesta->update([
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo_respuesta' => 'respuesta_abierta', // Todas las encuestas son de respuesta abierta
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'estado' => $validated['estado'],
            ]);

            // Eliminar todas las opciones si existen (las encuestas no usan opciones)
            \App\Models\EncuestaOpcion::where('encuesta_id', $encuesta->id)->delete();

            DB::commit();

            return redirect()->route('admin.encuestas-votaciones.index', ['tipo' => 'encuestas'])
                ->with('success', 'Encuesta actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al actualizar la encuesta: ' . $e->getMessage());
        }
    }

    /**
     * Update a votacion.
     */
    private function updateVotacion(Request $request, $id, $propiedad)
    {
        $votacion = Votacion::where('copropiedad_id', $propiedad->id)->findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estado' => 'required|in:activa,cerrada,anulada',
            'opciones' => 'required|array|min:2',
            'opciones.*' => 'required|string|max:255',
            'opciones_ids' => 'nullable|array',
            'opciones_ids.*' => 'nullable|integer|exists:votacion_opciones,id',
            'opciones_eliminar' => 'nullable|array',
            'opciones_eliminar.*' => 'integer|exists:votacion_opciones,id',
        ]);

        DB::beginTransaction();
        try {
            $votacion->update([
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'estado' => $validated['estado'],
            ]);

            // Eliminar opciones marcadas para eliminar
            if (isset($validated['opciones_eliminar'])) {
                \App\Models\VotacionOpcion::whereIn('id', $validated['opciones_eliminar'])
                    ->where('votacion_id', $votacion->id)
                    ->delete();
            }

            // Actualizar o crear opciones
            foreach ($validated['opciones'] as $index => $textoOpcion) {
                if (isset($validated['opciones_ids'][$index]) && $validated['opciones_ids'][$index]) {
                    // Actualizar opción existente
                    \App\Models\VotacionOpcion::where('id', $validated['opciones_ids'][$index])
                        ->where('votacion_id', $votacion->id)
                        ->update([
                            'texto_opcion' => $textoOpcion,
                            'orden' => $index + 1,
                        ]);
                } else {
                    // Crear nueva opción
                    \App\Models\VotacionOpcion::create([
                        'votacion_id' => $votacion->id,
                        'texto_opcion' => $textoOpcion,
                        'orden' => $index + 1,
                        'activo' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.encuestas-votaciones.index', ['tipo' => 'votaciones'])
                ->with('success', 'Votación actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al actualizar la votación: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified encuesta or votacion.
     */
    public function destroy($id, Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.encuestas-votaciones.index')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tipo = $request->get('tipo', 'encuesta');

        DB::beginTransaction();
        try {
            if ($tipo === 'encuesta') {
                $encuesta = Encuesta::where('copropiedad_id', $propiedad->id)->findOrFail($id);
                $encuesta->delete();
                $message = 'Encuesta eliminada exitosamente.';
            } else {
                $votacion = Votacion::where('copropiedad_id', $propiedad->id)->findOrFail($id);
                $votacion->delete();
                $message = 'Votación eliminada exitosamente.';
            }

            DB::commit();

            return redirect()->route('admin.encuestas-votaciones.index', ['tipo' => $tipo . 's'])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.encuestas-votaciones.index', ['tipo' => $tipo . 's'])
                ->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}
