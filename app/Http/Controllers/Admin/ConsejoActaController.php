<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsejoActaController extends Controller
{
    /**
     * Display a listing of actas (most recent first).
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = DB::table('consejo_actas')
            ->where('copropiedad_id', $propiedad->id)
            ->orderBy('fecha_acta', 'desc')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_acta', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_acta', '<=', $request->fecha_hasta);
        }

        $actas = $query->paginate(15)->appends($request->query());

        // Verificar firmas para cada acta
        foreach ($actas as $acta) {
            $acta->firmas_count = DB::table('consejo_acta_firmas')
                ->where('acta_id', $acta->id)
                ->count();
            $acta->puede_editar = $acta->firmas_count == 0;
        }

        return view('admin.consejo-actas.index', compact('actas', 'propiedad'));
    }

    /**
     * Show the form for creating a new acta.
     */
    public function create(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener reuniones de los últimos 3 meses
        $reuniones = DB::table('consejo_reuniones')
            ->where('copropiedad_id', $propiedad->id)
            ->where('fecha_inicio', '>=', Carbon::now()->subMonths(3))
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        // Si viene de una reunión específica
        $reunionId = $request->get('reunion_id');

        return view('admin.consejo-actas.create', compact('reuniones', 'reunionId', 'propiedad'));
    }

    /**
     * Store a newly created acta.
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'reunion_id' => 'required|exists:consejo_reuniones,id',
            'fecha_acta' => 'required|date',
            'quorum' => 'boolean',
            'contenido' => 'required|string',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:10240', // 10MB max
        ]);

        DB::beginTransaction();
        try {
            // Obtener datos de la reunión
            $reunion = DB::table('consejo_reuniones')
                ->where('id', $validated['reunion_id'])
                ->where('copropiedad_id', $propiedad->id)
                ->first();

            if (!$reunion) {
                throw new \Exception('Reunión no encontrada.');
            }

            // Crear acta
            $actaId = DB::table('consejo_actas')->insertGetId([
                'copropiedad_id' => $propiedad->id,
                'reunion_id' => $validated['reunion_id'],
                'tipo_reunion' => $reunion->tipo_reunion,
                'fecha_acta' => $validated['fecha_acta'],
                'quorum' => $validated['quorum'] ?? false,
                'contenido' => $validated['contenido'],
                'estado' => 'borrador',
                'visible_residentes' => false,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Subir archivos a Cloudinary
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/consejo/actas',
                            'resource_type' => 'auto',
                        ]);

                        DB::table('consejo_acta_archivos')->insert([
                            'acta_id' => $actaId,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'ruta_archivo' => $result['secure_url'] ?? $result['url'] ?? null,
                            'tipo_archivo' => $archivo->getMimeType(),
                            'tamaño' => $archivo->getSize(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de acta a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.consejo-actas.index')
                ->with('success', 'Acta creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear acta: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el acta.');
        }
    }

    /**
     * Display the specified acta.
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $acta = DB::table('consejo_actas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$acta) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'Acta no encontrada.');
        }

        // Obtener reunión
        $reunion = DB::table('consejo_reuniones')
            ->where('id', $acta->reunion_id)
            ->first();

        // Obtener firmas
        $firmas = DB::table('consejo_acta_firmas')
            ->join('consejo_integrantes', 'consejo_acta_firmas.integrante_id', '=', 'consejo_integrantes.id')
            ->where('consejo_acta_firmas.acta_id', $id)
            ->select('consejo_acta_firmas.*', 'consejo_integrantes.nombre', 'consejo_integrantes.cargo')
            ->get();

        // Obtener archivos
        $archivos = DB::table('consejo_acta_archivos')
            ->where('acta_id', $id)
            ->get();

        // Obtener decisiones
        $decisiones = DB::table('consejo_decisiones')
            ->where('acta_id', $id)
            ->get();

        // Verificar si puede editar/eliminar
        $puede_editar = $firmas->count() == 0;
        $puede_eliminar = $firmas->count() == 0;

        return view('admin.consejo-actas.show', compact('acta', 'reunion', 'firmas', 'archivos', 'decisiones', 'puede_editar', 'puede_eliminar', 'propiedad'));
    }

    /**
     * Show the form for editing the specified acta.
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $acta = DB::table('consejo_actas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$acta) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'Acta no encontrada.');
        }

        // Verificar si tiene firmas
        $firmasCount = DB::table('consejo_acta_firmas')
            ->where('acta_id', $id)
            ->count();

        if ($firmasCount > 0) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'No se puede editar un acta que ya tiene firmas.');
        }

        // Obtener archivos existentes
        $archivos = DB::table('consejo_acta_archivos')
            ->where('acta_id', $id)
            ->get();

        return view('admin.consejo-actas.edit', compact('acta', 'archivos', 'propiedad'));
    }

    /**
     * Update the specified acta.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $acta = DB::table('consejo_actas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$acta) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'Acta no encontrada.');
        }

        // Verificar si tiene firmas
        $firmasCount = DB::table('consejo_acta_firmas')
            ->where('acta_id', $id)
            ->count();

        if ($firmasCount > 0) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'No se puede editar un acta que ya tiene firmas.');
        }

        $validated = $request->validate([
            'fecha_acta' => 'required|date',
            'quorum' => 'boolean',
            'contenido' => 'required|string',
            'estado' => 'required|in:borrador,finalizada',
            'visible_residentes' => 'boolean',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:10240',
            'archivos_eliminar' => 'nullable|array',
            'archivos_eliminar.*' => 'integer|exists:consejo_acta_archivos,id',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar acta
            DB::table('consejo_actas')
                ->where('id', $id)
                ->update([
                    'fecha_acta' => $validated['fecha_acta'],
                    'quorum' => $validated['quorum'] ?? false,
                    'contenido' => $validated['contenido'],
                    'estado' => $validated['estado'],
                    'visible_residentes' => $validated['visible_residentes'] ?? false,
                    'updated_at' => now(),
                ]);

            // Eliminar archivos marcados
            if (!empty($validated['archivos_eliminar'])) {
                $archivosEliminar = DB::table('consejo_acta_archivos')
                    ->whereIn('id', $validated['archivos_eliminar'])
                    ->where('acta_id', $id)
                    ->get();

                foreach ($archivosEliminar as $archivo) {
                    // Intentar eliminar de Cloudinary si es posible
                    try {
                        $urlParts = explode('/', $archivo->ruta_archivo);
                        $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                        Cloudinary::uploadApi()->destroy('domoph/consejo/actas/' . $publicId);
                    } catch (\Exception $e) {
                        \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
                    }
                }

                DB::table('consejo_acta_archivos')
                    ->whereIn('id', $validated['archivos_eliminar'])
                    ->where('acta_id', $id)
                    ->delete();
            }

            // Subir nuevos archivos
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/consejo/actas',
                            'resource_type' => 'auto',
                        ]);

                        DB::table('consejo_acta_archivos')->insert([
                            'acta_id' => $id,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'ruta_archivo' => $result['secure_url'] ?? $result['url'] ?? null,
                            'tipo_archivo' => $archivo->getMimeType(),
                            'tamaño' => $archivo->getSize(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de acta a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.consejo-actas.index')
                ->with('success', 'Acta actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar acta: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el acta.');
        }
    }

    /**
     * Remove the specified acta.
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $acta = DB::table('consejo_actas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$acta) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'Acta no encontrada.');
        }

        // Verificar si tiene firmas
        $firmasCount = DB::table('consejo_acta_firmas')
            ->where('acta_id', $id)
            ->count();

        if ($firmasCount > 0) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'No se puede eliminar un acta que ya tiene firmas.');
        }

        DB::beginTransaction();
        try {
            // Eliminar archivos de Cloudinary
            $archivos = DB::table('consejo_acta_archivos')
                ->where('acta_id', $id)
                ->get();

            foreach ($archivos as $archivo) {
                try {
                    $urlParts = explode('/', $archivo->ruta_archivo);
                    $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                    Cloudinary::uploadApi()->destroy('domoph/consejo/actas/' . $publicId);
                } catch (\Exception $e) {
                    \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
                }
            }

            // Eliminar decisiones asociadas
            DB::table('consejo_decisiones')->where('acta_id', $id)->delete();

            // Eliminar archivos
            DB::table('consejo_acta_archivos')->where('acta_id', $id)->delete();

            // Eliminar acta
            DB::table('consejo_actas')->where('id', $id)->delete();

            DB::commit();

            return redirect()->route('admin.consejo-actas.index')
                ->with('success', 'Acta eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar acta: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar el acta.');
        }
    }

    /**
     * Firmar acta (desde el usuario del integrante).
     */
    public function firmar(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $acta = DB::table('consejo_actas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$acta) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'Acta no encontrada.');
        }

        // Obtener integrante del usuario actual
        $integrante = DB::table('consejo_integrantes')
            ->where('user_id', auth()->id())
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->first();

        if (!$integrante) {
            return redirect()->route('admin.consejo-actas.index')
                ->with('error', 'No eres un integrante activo del consejo.');
        }

        // Verificar si ya firmó
        $yaFirmo = DB::table('consejo_acta_firmas')
            ->where('acta_id', $id)
            ->where('integrante_id', $integrante->id)
            ->exists();

        if ($yaFirmo) {
            return redirect()->route('admin.consejo-actas.show', $id)
                ->with('error', 'Ya has firmado este acta.');
        }

        DB::beginTransaction();
        try {
            // Crear firma
            DB::table('consejo_acta_firmas')->insert([
                'acta_id' => $id,
                'integrante_id' => $integrante->id,
                'cargo' => $integrante->cargo,
                'fecha_firma' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Si todas las firmas están completas, cambiar estado a firmada
            $reunion = DB::table('consejo_reuniones')->where('id', $acta->reunion_id)->first();
            $asistencias = DB::table('consejo_reunion_asistencias')
                ->where('reunion_id', $acta->reunion_id)
                ->where('asistio', true)
                ->count();
            
            $firmasCount = DB::table('consejo_acta_firmas')
                ->where('acta_id', $id)
                ->count();

            if ($firmasCount >= $asistencias) {
                DB::table('consejo_actas')
                    ->where('id', $id)
                    ->update([
                        'estado' => 'firmada',
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return redirect()->route('admin.consejo-actas.show', $id)
                ->with('success', 'Acta firmada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al firmar acta: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al firmar el acta.');
        }
    }
}
