<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use App\Models\Licitacion;
use App\Models\LicitacionArchivo;
use App\Models\OfertaLicitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Carbon\Carbon;

class LicitacionController extends Controller
{
    /**
     * Display a listing of licitaciones.
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = Licitacion::where('copropiedad_id', $propiedad->id)
            ->with(['archivos', 'ofertas'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $licitaciones = $query->paginate(15);

        return view('admin.licitaciones.index', compact('licitaciones', 'propiedad'));
    }

    /**
     * Show the form for creating a new licitacion.
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.licitaciones.create', compact('propiedad'));
    }

    /**
     * Store a newly created licitacion.
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria' => 'required|in:mantenimiento,seguridad,servicios,obra_civil,tecnologia,otro',
            'presupuesto_estimado' => 'nullable|numeric|min:0',
            'fecha_publicacion' => 'nullable|date',
            'fecha_cierre' => 'required|date|after_or_equal:today',
            'estado' => 'required|in:borrador,publicada,cerrada,adjudicada,anulada',
            'visible_publicamente' => 'boolean',
            'archivos.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $licitacion = Licitacion::create([
                'copropiedad_id' => $propiedad->id,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'],
                'categoria' => $validated['categoria'],
                'presupuesto_estimado' => $validated['presupuesto_estimado'] ?? null,
                'fecha_publicacion' => $validated['fecha_publicacion'] ?? null,
                'fecha_cierre' => $validated['fecha_cierre'],
                'estado' => $validated['estado'],
                'visible_publicamente' => $request->has('visible_publicamente'),
                'creado_por' => auth()->id(),
                'activo' => true,
            ]);

            // Subir archivos a Cloudinary
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/licitaciones/' . $propiedad->id,
                            'resource_type' => 'auto',
                        ]);

                        LicitacionArchivo::create([
                            'licitacion_id' => $licitacion->id,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'url_archivo' => $result['secure_url'] ?? $result['url'] ?? null,
                            'tipo_archivo' => $archivo->getMimeType(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de licitación a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.licitaciones.index')
                ->with('success', 'Licitación creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear licitación: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la licitación. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified licitacion.
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $licitacion = Licitacion::where('copropiedad_id', $propiedad->id)
            ->with(['archivos', 'ofertas.archivos'])
            ->findOrFail($id);

        return view('admin.licitaciones.show', compact('licitacion', 'propiedad'));
    }

    /**
     * Show the form for editing the specified licitacion.
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $licitacion = Licitacion::where('copropiedad_id', $propiedad->id)
            ->with('archivos')
            ->findOrFail($id);

        return view('admin.licitaciones.edit', compact('licitacion', 'propiedad'));
    }

    /**
     * Update the specified licitacion.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $licitacion = Licitacion::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria' => 'required|in:mantenimiento,seguridad,servicios,obra_civil,tecnologia,otro',
            'presupuesto_estimado' => 'nullable|numeric|min:0',
            'fecha_publicacion' => 'nullable|date',
            'fecha_cierre' => 'required|date',
            'estado' => 'required|in:borrador,publicada,cerrada,adjudicada,anulada',
            'visible_publicamente' => 'boolean',
            'archivos.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
            'archivos_eliminar.*' => 'integer|exists:licitacion_archivos,id',
        ]);

        DB::beginTransaction();
        try {
            $licitacion->update([
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'],
                'categoria' => $validated['categoria'],
                'presupuesto_estimado' => $validated['presupuesto_estimado'] ?? null,
                'fecha_publicacion' => $validated['fecha_publicacion'] ?? null,
                'fecha_cierre' => $validated['fecha_cierre'],
                'estado' => $validated['estado'],
                'visible_publicamente' => $request->has('visible_publicamente'),
            ]);

            // Eliminar archivos marcados
            if ($request->filled('archivos_eliminar')) {
                $archivosEliminar = array_filter(array_map('intval', $request->archivos_eliminar));
                if (!empty($archivosEliminar)) {
                    $archivos = LicitacionArchivo::whereIn('id', $archivosEliminar)
                        ->where('licitacion_id', $licitacion->id)
                        ->get();
                    
                    foreach ($archivos as $archivo) {
                        try {
                            // Extraer public_id de la URL de Cloudinary
                            $urlParts = explode('/', $archivo->url_archivo);
                            $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                            $folder = 'domoph/licitaciones/' . $propiedad->id;
                            Cloudinary::uploadApi()->destroy($folder . '/' . $publicId);
                        } catch (\Exception $e) {
                            \Log::error('Error al eliminar archivo de Cloudinary: ' . $e->getMessage());
                        }
                    }
                    
                    LicitacionArchivo::whereIn('id', $archivosEliminar)->delete();
                }
            }

            // Subir nuevos archivos
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/licitaciones/' . $propiedad->id,
                            'resource_type' => 'auto',
                        ]);

                        LicitacionArchivo::create([
                            'licitacion_id' => $licitacion->id,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'url_archivo' => $result['secure_url'] ?? $result['url'] ?? null,
                            'tipo_archivo' => $archivo->getMimeType(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de licitación a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.licitaciones.index')
                ->with('success', 'Licitación actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar licitación: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la licitación. Por favor, intente nuevamente.');
        }
    }

    /**
     * Remove the specified licitacion.
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $licitacion = Licitacion::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            // Eliminar archivos de Cloudinary
            foreach ($licitacion->archivos as $archivo) {
                try {
                    $urlParts = explode('/', $archivo->url_archivo);
                    $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                    $folder = 'domoph/licitaciones/' . $propiedad->id;
                    Cloudinary::uploadApi()->destroy($folder . '/' . $publicId);
                } catch (\Exception $e) {
                    \Log::error('Error al eliminar archivo de Cloudinary: ' . $e->getMessage());
                }
            }

            $licitacion->delete();

            DB::commit();

            return redirect()->route('admin.licitaciones.index')
                ->with('success', 'Licitación eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar licitación: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar la licitación. Por favor, intente nuevamente.');
        }
    }

    /**
     * Obtener detalles de una oferta (JSON)
     */
    public function getOferta($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json(['error' => 'No hay propiedad asignada.'], 404);
        }

        $oferta = OfertaLicitacion::whereHas('licitacion', function($query) use ($propiedad) {
                $query->where('copropiedad_id', $propiedad->id);
            })
            ->with('archivos')
            ->findOrFail($id);

        return response()->json([
            'id' => $oferta->id,
            'nombre_proveedor' => $oferta->nombre_proveedor,
            'nit_proveedor' => $oferta->nit_proveedor,
            'email_contacto' => $oferta->email_contacto,
            'telefono_contacto' => $oferta->telefono_contacto,
            'descripcion_oferta' => $oferta->descripcion_oferta,
            'valor_ofertado' => $oferta->valor_ofertado,
            'estado' => $oferta->estado,
            'fecha_postulacion' => $oferta->fecha_postulacion->format('d/m/Y'),
            'es_ganadora' => $oferta->es_ganadora,
            'archivos' => $oferta->archivos->map(function($archivo) {
                return [
                    'id' => $archivo->id,
                    'nombre_archivo' => $archivo->nombre_archivo,
                    'url_archivo' => $archivo->url_archivo,
                ];
            }),
        ]);
    }

    /**
     * Adjudicar una oferta (marcar como ganadora y cerrar la licitación).
     */
    public function adjudicar(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'oferta_id' => 'required|exists:ofertas_licitacion,id',
        ]);

        $oferta = OfertaLicitacion::findOrFail($validated['oferta_id']);
        
        // Verificar que la oferta pertenece a una licitación de la propiedad
        $licitacion = Licitacion::where('copropiedad_id', $propiedad->id)
            ->findOrFail($oferta->licitacion_id);

        // Verificar que la licitación no esté ya cerrada o adjudicada
        if (in_array($licitacion->estado, ['cerrada', 'adjudicada'])) {
            return redirect()->back()
                ->with('error', 'Esta licitación ya está cerrada o adjudicada.');
        }

        DB::beginTransaction();
        try {
            // Marcar la oferta como ganadora
            $oferta->update([
                'es_ganadora' => true,
                'estado' => 'seleccionada',
            ]);

            // Cerrar la licitación y ocultarla públicamente
            $licitacion->update([
                'estado' => 'cerrada',
                'visible_publicamente' => false,
            ]);

            DB::commit();

            return redirect()->route('admin.licitaciones.show', $licitacion->id)
                ->with('success', 'Oferta adjudicada exitosamente. La licitación ha sido cerrada y ocultada públicamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al adjudicar oferta: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al adjudicar la oferta. Por favor, intente nuevamente.');
        }
    }
}
