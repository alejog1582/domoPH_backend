<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comunicado;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ComunicadoController extends Controller
{
    /**
     * Mostrar la lista de comunicados
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Query base: comunicados con sus relaciones
        $query = Comunicado::with(['autor'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('activo', true);

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por estado de publicación
        if ($request->filled('publicado')) {
            $query->where('publicado', $request->publicado == '1');
        }

        // Filtro por visibilidad
        if ($request->filled('visible_para')) {
            $query->where('visible_para', $request->visible_para);
        }

        // Filtro por búsqueda en título o contenido
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('contenido', 'like', "%{$buscar}%")
                  ->orWhere('resumen', 'like', "%{$buscar}%");
            });
        }

        // Filtro por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_publicacion', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_publicacion', '<=', $request->fecha_hasta);
        }

        // Ordenar por fecha de publicación descendente (más reciente primero)
        $comunicados = $query->orderBy('fecha_publicacion', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.comunicados.index', compact('comunicados', 'propiedad'));
    }

    /**
     * Mostrar el formulario de creación de un comunicado
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.comunicados.create', compact('propiedad'));
    }

    /**
     * Guardar un nuevo comunicado
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
            'slug' => 'nullable|string|max:220',
            'contenido' => 'required|string',
            'resumen' => 'nullable|string|max:500',
            'tipo' => 'required|in:general,urgente,informativo,mantenimiento',
            'publicado' => 'boolean',
            'fecha_publicacion' => 'nullable|date',
            'visible_para' => 'required|in:todos,propietarios,arrendatarios,administracion',
            'imagen_portada' => 'nullable|string|max:255',
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede exceder 200 caracteres.',
            'contenido.required' => 'El contenido es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'visible_para.required' => 'La visibilidad es obligatoria.',
            'visible_para.in' => 'La visibilidad seleccionada no es válida.',
        ]);

        try {
            // Generar slug si no se proporciona
            $slug = $validated['slug'] ?? Str::slug($validated['titulo']);
            
            // Asegurar unicidad del slug
            $originalSlug = $slug;
            $count = 1;
            while (Comunicado::where('copropiedad_id', $propiedad->id)
                ->where('slug', $slug)
                ->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            // Si está publicado y no tiene fecha de publicación, usar la fecha actual
            $fechaPublicacion = null;
            if ($validated['publicado'] ?? false) {
                $fechaPublicacion = $validated['fecha_publicacion'] ?? Carbon::now();
            }

            $comunicado = Comunicado::create([
                'copropiedad_id' => $propiedad->id,
                'titulo' => $validated['titulo'],
                'slug' => $slug,
                'contenido' => $validated['contenido'],
                'resumen' => $validated['resumen'] ?? null,
                'tipo' => $validated['tipo'],
                'publicado' => $validated['publicado'] ?? false,
                'fecha_publicacion' => $fechaPublicacion,
                'visible_para' => $validated['visible_para'],
                'imagen_portada' => $validated['imagen_portada'] ?? null,
                'autor_id' => Auth::id(),
                'activo' => true,
            ]);

            return redirect()->route('admin.comunicados.index')
                ->with('success', 'Comunicado creado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear comunicado: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el comunicado: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario de edición de un comunicado
     */
    public function edit(Comunicado $comunicado)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que el comunicado pertenece a la propiedad activa
        if ($comunicado->copropiedad_id !== $propiedad->id) {
            return redirect()->route('admin.comunicados.index')
                ->with('error', 'No tienes permiso para editar este comunicado.');
        }

        return view('admin.comunicados.edit', compact('comunicado', 'propiedad'));
    }

    /**
     * Actualizar un comunicado existente
     */
    public function update(Request $request, Comunicado $comunicado)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que el comunicado pertenece a la propiedad activa
        if ($comunicado->copropiedad_id !== $propiedad->id) {
            return redirect()->route('admin.comunicados.index')
                ->with('error', 'No tienes permiso para editar este comunicado.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'slug' => 'nullable|string|max:220',
            'contenido' => 'required|string',
            'resumen' => 'nullable|string|max:500',
            'tipo' => 'required|in:general,urgente,informativo,mantenimiento',
            'publicado' => 'boolean',
            'destacado' => 'boolean',
            'fecha_publicacion' => 'nullable|date',
            'visible_para' => 'required|in:todos,propietarios,arrendatarios,administracion',
            'imagen_portada' => 'nullable|string|max:255',
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede exceder 200 caracteres.',
            'contenido.required' => 'El contenido es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'visible_para.required' => 'La visibilidad es obligatoria.',
            'visible_para.in' => 'La visibilidad seleccionada no es válida.',
        ]);

        try {
            // Generar slug si no se proporciona o si cambió el título
            $slug = $validated['slug'] ?? Str::slug($validated['titulo']);
            
            // Si el slug cambió, asegurar unicidad
            if ($slug !== $comunicado->slug) {
                $originalSlug = $slug;
                $count = 1;
                while (Comunicado::where('copropiedad_id', $propiedad->id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $comunicado->id)
                    ->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }

            // Si está publicado y no tiene fecha de publicación, usar la fecha actual
            $fechaPublicacion = $comunicado->fecha_publicacion;
            if ($validated['publicado'] ?? false) {
                $fechaPublicacion = $validated['fecha_publicacion'] ?? ($comunicado->fecha_publicacion ?? Carbon::now());
            } elseif (!($validated['publicado'] ?? false)) {
                // Si se despublica, mantener la fecha pero no mostrar como publicado
                $fechaPublicacion = $comunicado->fecha_publicacion;
            }

            $comunicado->update([
                'titulo' => $validated['titulo'],
                'slug' => $slug,
                'contenido' => $validated['contenido'],
                'resumen' => $validated['resumen'] ?? null,
                'tipo' => $validated['tipo'],
                'publicado' => $validated['publicado'] ?? false,
                'destacado' => $validated['destacado'] ?? false,
                'fecha_publicacion' => $fechaPublicacion,
                'visible_para' => $validated['visible_para'],
                'imagen_portada' => $validated['imagen_portada'] ?? null,
            ]);

            return redirect()->route('admin.comunicados.index')
                ->with('success', 'Comunicado actualizado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar comunicado: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el comunicado: ' . $e->getMessage())
                ->withInput();
        }
    }
}
