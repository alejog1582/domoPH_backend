<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZonaSocial;
use App\Models\ZonaSocialHorario;
use App\Models\ZonaSocialImagen;
use App\Models\ZonaSocialRegla;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ZonaSocialController extends Controller
{
    /**
     * Mostrar la lista de zonas sociales
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = ZonaSocial::where('propiedad_id', $propiedad->id)
            ->with(['imagenes', 'horarios', 'reglas']);

        // Búsqueda general
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('ubicacion', 'like', "%{$buscar}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por activo
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo == '1');
        }

        $zonasSociales = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.zonas-sociales.index', compact('zonasSociales', 'propiedad'));
    }

    /**
     * Mostrar el formulario de creación de una zona social
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.zonas-sociales.create', compact('propiedad'));
    }

    /**
     * Almacenar una nueva zona social
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'ubicacion' => 'nullable|string|max:150',
            'capacidad_maxima' => 'required|integer|min:1',
            'max_invitados_por_reserva' => 'nullable|integer|min:0',
            'tiempo_minimo_uso_horas' => 'required|integer|min:1',
            'tiempo_maximo_uso_horas' => 'required|integer|min:1',
            'reservas_simultaneas' => 'nullable|boolean',
            'valor_alquiler' => 'nullable|numeric|min:0',
            'valor_deposito' => 'nullable|numeric|min:0',
            'requiere_aprobacion' => 'boolean',
            'permite_reservas_en_mora' => 'boolean',
            'reglamento_url' => 'nullable|url|max:255',
            'estado' => 'required|in:activa,inactiva,mantenimiento',
            'activo' => 'boolean',
            // Horarios
            'horarios' => 'nullable|array',
            'horarios.*.dia_semana' => 'required|in:lunes,martes,miércoles,jueves,viernes,sábado,domingo',
            'horarios.*.hora_inicio' => 'required|date_format:H:i',
            'horarios.*.hora_fin' => 'required|date_format:H:i|after:horarios.*.hora_inicio',
            'horarios.*.activo' => 'boolean',
            // Imágenes
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            // Reglas
            'reglas' => 'nullable|array',
            'reglas.*.clave' => 'required|string|max:100',
            'reglas.*.valor' => 'required|string|max:255',
            'reglas.*.descripcion' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Crear la zona social
            $zonaSocial = ZonaSocial::create([
                'propiedad_id' => $propiedad->id,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'ubicacion' => $validated['ubicacion'] ?? null,
                'capacidad_maxima' => $validated['capacidad_maxima'],
                'max_invitados_por_reserva' => $validated['max_invitados_por_reserva'] ?? null,
                'tiempo_minimo_uso_horas' => $validated['tiempo_minimo_uso_horas'],
                'tiempo_maximo_uso_horas' => $validated['tiempo_maximo_uso_horas'],
                'reservas_simultaneas' => $validated['reservas_simultaneas'] ?? true,
                'valor_alquiler' => $validated['valor_alquiler'] ?? null,
                'valor_deposito' => $validated['valor_deposito'] ?? null,
                'requiere_aprobacion' => $request->has('requiere_aprobacion'),
                'permite_reservas_en_mora' => $request->has('permite_reservas_en_mora'),
                'reglamento_url' => $validated['reglamento_url'] ?? null,
                'estado' => $validated['estado'],
                'activo' => $request->has('activo'),
            ]);

            // Crear horarios
            if ($request->has('horarios') && is_array($request->horarios)) {
                foreach ($request->horarios as $index => $horario) {
                    if (!empty($horario['dia_semana']) && !empty($horario['hora_inicio']) && !empty($horario['hora_fin'])) {
                        ZonaSocialHorario::create([
                            'zona_social_id' => $zonaSocial->id,
                            'dia_semana' => $horario['dia_semana'],
                            'hora_inicio' => $horario['hora_inicio'],
                            'hora_fin' => $horario['hora_fin'],
                            'activo' => isset($horario['activo']) ? true : true,
                        ]);
                    }
                }
            }

            // Subir y crear imágenes
            if ($request->hasFile('imagenes')) {
                $orden = 0;
                foreach ($request->file('imagenes') as $imagen) {
                    if ($imagen && $imagen->isValid()) {
                        try {
                            $result = Cloudinary::uploadApi()->upload($imagen->getRealPath(), [
                                'folder' => 'damoph/zonas-sociales',
                                'public_id' => 'zona_social_' . $zonaSocial->id . '_' . time() . '_' . $orden,
                            ]);
                            
                            ZonaSocialImagen::create([
                                'zona_social_id' => $zonaSocial->id,
                                'url_imagen' => $result['secure_url'] ?? $result['url'] ?? null,
                                'orden' => $orden,
                                'activo' => true,
                            ]);
                            
                            $orden++;
                        } catch (\Exception $e) {
                            \Log::error('Error al subir imagen de zona social a Cloudinary: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Crear reglas
            if ($request->has('reglas') && is_array($request->reglas)) {
                foreach ($request->reglas as $regla) {
                    if (!empty($regla['clave']) && !empty($regla['valor'])) {
                        ZonaSocialRegla::create([
                            'zona_social_id' => $zonaSocial->id,
                            'clave' => $regla['clave'],
                            'valor' => $regla['valor'],
                            'descripcion' => $regla['descripcion'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.zonas-sociales.index')
                ->with('success', 'Zona común creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear zona social: ' . $e->getMessage());
            return back()->with('error', 'Error al crear la zona común: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mostrar el formulario de edición de una zona social
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $zonaSocial = ZonaSocial::where('propiedad_id', $propiedad->id)
            ->with(['horarios', 'imagenes', 'reglas'])
            ->findOrFail($id);

        return view('admin.zonas-sociales.edit', compact('zonaSocial', 'propiedad'));
    }

    /**
     * Actualizar una zona social
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $zonaSocial = ZonaSocial::where('propiedad_id', $propiedad->id)
            ->findOrFail($id);

        // Filter out empty values from arrays before validation
        $imagenesEliminar = array_filter(
            array_map('intval', $request->input('imagenes_eliminar', [])),
            function($value) {
                return $value > 0;
            }
        );
        
        $reglasEliminar = array_filter(
            array_map('intval', $request->input('reglas_eliminar', [])),
            function($value) {
                return $value > 0;
            }
        );
        
        $request->merge([
            'imagenes_eliminar' => array_values($imagenesEliminar),
            'reglas_eliminar' => array_values($reglasEliminar),
        ]);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'ubicacion' => 'nullable|string|max:150',
            'capacidad_maxima' => 'required|integer|min:1',
            'max_invitados_por_reserva' => 'nullable|integer|min:0',
            'tiempo_minimo_uso_horas' => 'required|integer|min:1',
            'tiempo_maximo_uso_horas' => 'required|integer|min:1',
            'reservas_simultaneas' => 'nullable|boolean',
            'valor_alquiler' => 'nullable|numeric|min:0',
            'valor_deposito' => 'nullable|numeric|min:0',
            'requiere_aprobacion' => 'boolean',
            'permite_reservas_en_mora' => 'boolean',
            'reglamento_url' => 'nullable|url|max:255',
            'estado' => 'required|in:activa,inactiva,mantenimiento',
            'activo' => 'boolean',
            // Horarios
            'horarios' => 'nullable|array',
            'horarios.*.dia_semana' => 'required|in:lunes,martes,miércoles,jueves,viernes,sábado,domingo',
            'horarios.*.hora_inicio' => 'required|date_format:H:i',
            'horarios.*.hora_fin' => 'required|date_format:H:i|after:horarios.*.hora_inicio',
            'horarios.*.activo' => 'boolean',
            // Imágenes nuevas
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            // IDs de imágenes a eliminar
            'imagenes_eliminar' => 'nullable|array',
            'imagenes_eliminar.*' => 'integer|exists:zona_social_imagenes,id',
            // Reglas
            'reglas' => 'nullable|array',
            'reglas.*.clave' => 'required|string|max:100',
            'reglas.*.valor' => 'required|string|max:255',
            'reglas.*.descripcion' => 'nullable|string|max:255',
            // IDs de reglas a eliminar
            'reglas_eliminar' => 'nullable|array',
            'reglas_eliminar.*' => 'integer|exists:zona_social_reglas,id',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar la zona social
            $zonaSocial->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'ubicacion' => $validated['ubicacion'] ?? null,
                'capacidad_maxima' => $validated['capacidad_maxima'],
                'max_invitados_por_reserva' => $validated['max_invitados_por_reserva'] ?? null,
                'tiempo_minimo_uso_horas' => $validated['tiempo_minimo_uso_horas'],
                'tiempo_maximo_uso_horas' => $validated['tiempo_maximo_uso_horas'],
                'reservas_simultaneas' => $validated['reservas_simultaneas'] ?? true,
                'valor_alquiler' => $validated['valor_alquiler'] ?? null,
                'valor_deposito' => $validated['valor_deposito'] ?? null,
                'requiere_aprobacion' => $request->has('requiere_aprobacion'),
                'permite_reservas_en_mora' => $request->has('permite_reservas_en_mora'),
                'reglamento_url' => $validated['reglamento_url'] ?? null,
                'estado' => $validated['estado'],
                'activo' => $request->has('activo'),
            ]);

            // Procesar horarios: primero crear los nuevos, luego eliminar los antiguos
            $horariosCreados = [];
            if ($request->has('horarios') && is_array($request->horarios) && count($request->horarios) > 0) {
                // Array para rastrear horarios únicos y evitar duplicados
                $horariosUnicos = [];
                
                foreach ($request->horarios as $horario) {
                    if (!empty($horario['dia_semana']) && !empty($horario['hora_inicio']) && !empty($horario['hora_fin'])) {
                        // Crear clave única para el horario
                        $claveUnica = strtolower($horario['dia_semana']) . '-' . $horario['hora_inicio'] . '-' . $horario['hora_fin'];
                        
                        // Solo crear si no existe ya este horario
                        if (!isset($horariosUnicos[$claveUnica])) {
                            try {
                                $horarioCreado = ZonaSocialHorario::create([
                                    'zona_social_id' => $zonaSocial->id,
                                    'dia_semana' => $horario['dia_semana'],
                                    'hora_inicio' => $horario['hora_inicio'],
                                    'hora_fin' => $horario['hora_fin'],
                                    'activo' => isset($horario['activo']) ? true : true,
                                ]);
                                
                                $horariosCreados[] = $horarioCreado->id;
                                // Marcar como procesado
                                $horariosUnicos[$claveUnica] = true;
                            } catch (\Illuminate\Database\QueryException $e) {
                                // Si hay error de duplicado, continuar sin insertar
                                if ($e->getCode() == 23000) {
                                    \Log::warning('Intento de crear horario duplicado: ' . $claveUnica . ' para zona ' . $zonaSocial->id);
                                    continue;
                                }
                                throw $e;
                            }
                        }
                    }
                }
                
                // Solo eliminar horarios antiguos si se crearon nuevos horarios exitosamente
                if (count($horariosCreados) > 0) {
                    $zonaSocial->todosLosHorarios()
                        ->whereNotIn('id', $horariosCreados)
                        ->delete();
                }
            } else {
                // Si no se enviaron horarios, eliminar todos los existentes
                $zonaSocial->todosLosHorarios()->delete();
            }

            // Eliminar imágenes marcadas
            if ($request->has('imagenes_eliminar')) {
                ZonaSocialImagen::whereIn('id', $request->imagenes_eliminar)
                    ->where('zona_social_id', $zonaSocial->id)
                    ->delete();
            }

            // Subir nuevas imágenes
            if ($request->hasFile('imagenes')) {
                $maxOrden = $zonaSocial->todasLasImagenes()->max('orden') ?? -1;
                $orden = $maxOrden + 1;
                
                foreach ($request->file('imagenes') as $imagen) {
                    if ($imagen && $imagen->isValid()) {
                        try {
                            $result = Cloudinary::uploadApi()->upload($imagen->getRealPath(), [
                                'folder' => 'damoph/zonas-sociales',
                                'public_id' => 'zona_social_' . $zonaSocial->id . '_' . time() . '_' . $orden,
                            ]);
                            
                            ZonaSocialImagen::create([
                                'zona_social_id' => $zonaSocial->id,
                                'url_imagen' => $result['secure_url'] ?? $result['url'] ?? null,
                                'orden' => $orden,
                                'activo' => true,
                            ]);
                            
                            $orden++;
                        } catch (\Exception $e) {
                            \Log::error('Error al subir imagen de zona social a Cloudinary: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Eliminar reglas marcadas
            if ($request->has('reglas_eliminar')) {
                ZonaSocialRegla::whereIn('id', $request->reglas_eliminar)
                    ->where('zona_social_id', $zonaSocial->id)
                    ->delete();
            }

            // Eliminar todas las reglas existentes y crear nuevas
            $zonaSocial->reglas()->delete();
            if ($request->has('reglas') && is_array($request->reglas)) {
                foreach ($request->reglas as $regla) {
                    if (!empty($regla['clave']) && !empty($regla['valor'])) {
                        ZonaSocialRegla::create([
                            'zona_social_id' => $zonaSocial->id,
                            'clave' => $regla['clave'],
                            'valor' => $regla['valor'],
                            'descripcion' => $regla['descripcion'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.zonas-sociales.index')
                ->with('success', 'Zona común actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar zona social: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar la zona común: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Eliminar una zona social
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $zonaSocial = ZonaSocial::where('propiedad_id', $propiedad->id)
            ->findOrFail($id);

        try {
            $zonaSocial->delete();

            return redirect()->route('admin.zonas-sociales.index')
                ->with('success', 'Zona común eliminada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar zona social: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la zona común: ' . $e->getMessage());
        }
    }
}
