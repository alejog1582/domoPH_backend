<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\SeguimientoSolicitudComercial;
use App\Models\SolicitudComercial;
use App\Models\SolicitudSeguimiento;
use App\Models\SolicitudArchivo;
use App\Models\User;
use App\Models\LogAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SolicitudComercialController extends Controller
{
    /**
     * Listar todas las solicitudes comerciales
     */
    public function index(Request $request)
    {
        $query = SolicitudComercial::with(['asignadoA', 'seguimientos', 'archivos']);

        // Filtros
        if ($request->filled('tipo_solicitud')) {
            $query->where('tipo_solicitud', $request->tipo_solicitud);
        }

        if ($request->filled('estado_gestion')) {
            $query->where('estado_gestion', $request->estado_gestion);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('asignado_a')) {
            $query->where('asignado_a_user_id', $request->asignado_a);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_contacto', 'like', "%{$search}%")
                  ->orWhere('empresa', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        // Por defecto mostrar solo pendientes y en_proceso si no hay filtro de estado
        if (!$request->filled('estado_gestion')) {
            $query->whereIn('estado_gestion', ['pendiente', 'en_proceso']);
        }

        // Solo activas por defecto
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo == '1');
        } else {
            $query->where('activo', true);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $solicitudes = $query->paginate($perPage);

        // Obtener usuarios para el filtro de asignados
        $usuarios = User::where('perfil', 'superadministrador')
            ->orWhere('perfil', 'administrador')
            ->orderBy('nombre')
            ->get();

        // Registrar auditoría
        LogAuditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'list',
            'modelo' => 'SolicitudComercial',
            'descripcion' => 'Listado de solicitudes comerciales',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'modulo' => 'SuperAdmin',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $solicitudes,
                'message' => 'Solicitudes obtenidas exitosamente'
            ]);
        }

        return view('superadmin.solicitudes-comerciales.index', compact('solicitudes', 'usuarios'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $usuarios = User::where('perfil', 'superadministrador')
            ->orWhere('perfil', 'administrador')
            ->orderBy('nombre')
            ->get();

        return view('superadmin.solicitudes-comerciales.create', compact('usuarios'));
    }

    /**
     * Crear una nueva solicitud comercial
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_solicitud' => 'required|in:cotizacion,demo,contacto',
            'nombre_contacto' => 'required|string|max:200',
            'empresa' => 'nullable|string|max:200',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:50',
            'ciudad' => 'nullable|string|max:100',
            'pais' => 'nullable|string|max:100',
            'mensaje' => 'required|string',
            'origen' => 'nullable|in:landing,web,whatsapp,referido,otro',
            'estado_gestion' => 'nullable|in:pendiente,en_proceso,contactado,cerrado_ganado,cerrado_perdido',
            'prioridad' => 'nullable|in:baja,media,alta',
            'asignado_a_user_id' => 'nullable|exists:users,id',
            'archivos.*' => 'nullable|file|max:10240', // 10MB máximo
        ]);

        DB::beginTransaction();
        try {
            $solicitud = SolicitudComercial::create([
                'tipo_solicitud' => $validated['tipo_solicitud'],
                'nombre_contacto' => $validated['nombre_contacto'],
                'empresa' => $validated['empresa'] ?? null,
                'email' => $validated['email'],
                'telefono' => $validated['telefono'],
                'ciudad' => $validated['ciudad'] ?? null,
                'pais' => $validated['pais'] ?? null,
                'mensaje' => $validated['mensaje'],
                'origen' => $validated['origen'] ?? null,
                'estado_gestion' => $validated['estado_gestion'] ?? 'pendiente',
                'prioridad' => $validated['prioridad'] ?? 'media',
                'asignado_a_user_id' => $validated['asignado_a_user_id'] ?? null,
            ]);

            // Subir archivos si existen
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/solicitudes-comerciales',
                            'resource_type' => 'auto',
                        ]);

                        SolicitudArchivo::create([
                            'solicitud_comercial_id' => $solicitud->id,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'ruta_archivo' => $result['secure_url'],
                            'tipo_mime' => $archivo->getMimeType(),
                            'tamaño' => $archivo->getSize(),
                            'cargado_por_user_id' => auth()->id(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de solicitud comercial a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'create',
                'modelo' => 'SolicitudComercial',
                'modelo_id' => $solicitud->id,
                'descripcion' => "Solicitud comercial creada: {$solicitud->nombre_contacto}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $solicitud->load(['asignadoA', 'archivos']),
                    'message' => 'Solicitud comercial creada exitosamente'
                ], 201);
            }

            return redirect()->route('superadmin.solicitudes-comerciales.show', $solicitud->id)
                ->with('success', 'Solicitud comercial creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear solicitud comercial: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la solicitud comercial'
                ], 500);
            }

            return back()->withInput()->with('error', 'Error al crear la solicitud comercial.');
        }
    }

    /**
     * Mostrar detalle de una solicitud comercial
     */
    public function show($id)
    {
        $solicitud = SolicitudComercial::with(['asignadoA', 'seguimientos.usuario', 'archivos.cargadoPor'])
            ->findOrFail($id);

        $usuarios = User::where('perfil', 'superadministrador')
            ->orWhere('perfil', 'administrador')
            ->orderBy('nombre')
            ->get();

        return view('superadmin.solicitudes-comerciales.show', compact('solicitud', 'usuarios'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $solicitud = SolicitudComercial::findOrFail($id);
        $usuarios = User::where('perfil', 'superadministrador')
            ->orWhere('perfil', 'administrador')
            ->orderBy('nombre')
            ->get();

        return view('superadmin.solicitudes-comerciales.edit', compact('solicitud', 'usuarios'));
    }

    /**
     * Actualizar una solicitud comercial
     */
    public function update(Request $request, $id)
    {
        $solicitud = SolicitudComercial::findOrFail($id);

        $validated = $request->validate([
            'tipo_solicitud' => 'required|in:cotizacion,demo,contacto',
            'nombre_contacto' => 'required|string|max:200',
            'empresa' => 'nullable|string|max:200',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:50',
            'ciudad' => 'nullable|string|max:100',
            'pais' => 'nullable|string|max:100',
            'mensaje' => 'required|string',
            'origen' => 'nullable|in:landing,web,whatsapp,referido,otro',
            'estado_gestion' => 'required|in:pendiente,en_proceso,contactado,cerrado_ganado,cerrado_perdido',
            'prioridad' => 'required|in:baja,media,alta',
            'fecha_contacto' => 'nullable|date',
            'asignado_a_user_id' => 'nullable|exists:users,id',
            'activo' => 'boolean',
            'archivos.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $solicitud->update([
                'tipo_solicitud' => $validated['tipo_solicitud'],
                'nombre_contacto' => $validated['nombre_contacto'],
                'empresa' => $validated['empresa'] ?? null,
                'email' => $validated['email'],
                'telefono' => $validated['telefono'],
                'ciudad' => $validated['ciudad'] ?? null,
                'pais' => $validated['pais'] ?? null,
                'mensaje' => $validated['mensaje'],
                'origen' => $validated['origen'] ?? null,
                'estado_gestion' => $validated['estado_gestion'],
                'prioridad' => $validated['prioridad'],
                'fecha_contacto' => $validated['fecha_contacto'] ?? null,
                'asignado_a_user_id' => $validated['asignado_a_user_id'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]);

            // Subir nuevos archivos si existen
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/solicitudes-comerciales',
                            'resource_type' => 'auto',
                        ]);

                        SolicitudArchivo::create([
                            'solicitud_comercial_id' => $solicitud->id,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'ruta_archivo' => $result['secure_url'],
                            'tipo_mime' => $archivo->getMimeType(),
                            'tamaño' => $archivo->getSize(),
                            'cargado_por_user_id' => auth()->id(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de solicitud comercial a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'update',
                'modelo' => 'SolicitudComercial',
                'modelo_id' => $solicitud->id,
                'descripcion' => "Solicitud comercial actualizada: {$solicitud->nombre_contacto}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $solicitud->load(['asignadoA', 'archivos']),
                    'message' => 'Solicitud comercial actualizada exitosamente'
                ]);
            }

            return redirect()->route('superadmin.solicitudes-comerciales.show', $solicitud->id)
                ->with('success', 'Solicitud comercial actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar solicitud comercial: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la solicitud comercial'
                ], 500);
            }

            return back()->withInput()->with('error', 'Error al actualizar la solicitud comercial.');
        }
    }

    /**
     * Eliminar una solicitud comercial (soft delete)
     */
    public function destroy($id)
    {
        $solicitud = SolicitudComercial::findOrFail($id);

        DB::beginTransaction();
        try {
            // Eliminar archivos de Cloudinary
            foreach ($solicitud->archivos as $archivo) {
                try {
                    // Extraer public_id de la URL de Cloudinary
                    $urlParts = parse_url($archivo->ruta_archivo);
                    $pathParts = explode('/', trim($urlParts['path'], '/'));
                    $publicId = end($pathParts);
                    $publicId = str_replace('.' . pathinfo($publicId, PATHINFO_EXTENSION), '', $publicId);
                    
                    Cloudinary::uploadApi()->destroy('domoph/solicitudes-comerciales/' . $publicId);
                } catch (\Exception $e) {
                    \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
                }
            }

            $solicitud->delete();

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'delete',
                'modelo' => 'SolicitudComercial',
                'modelo_id' => $solicitud->id,
                'descripcion' => "Solicitud comercial eliminada: {$solicitud->nombre_contacto}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud comercial eliminada exitosamente'
                ]);
            }

            return redirect()->route('superadmin.solicitudes-comerciales.index')
                ->with('success', 'Solicitud comercial eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar solicitud comercial: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la solicitud comercial'
                ], 500);
            }

            return back()->with('error', 'Error al eliminar la solicitud comercial.');
        }
    }

    /**
     * Agregar seguimiento a una solicitud comercial
     */
    public function agregarSeguimiento(Request $request, $id)
    {
        $solicitud = SolicitudComercial::findOrFail($id);

        $validated = $request->validate([
            'comentario' => 'required|string',
            'estado_resultante' => 'nullable|in:pendiente,en_proceso,contactado,cerrado_ganado,cerrado_perdido',
            'proximo_contacto' => 'nullable|date',
            'archivos.*' => 'nullable|file|max:10240', // 10MB máximo
        ]);

        DB::beginTransaction();
        try {
            $seguimiento = SolicitudSeguimiento::create([
                'solicitud_comercial_id' => $solicitud->id,
                'user_id' => auth()->id(),
                'comentario' => $validated['comentario'],
                'estado_resultante' => $validated['estado_resultante'] ?? null,
                'proximo_contacto' => $validated['proximo_contacto'] ?? null,
            ]);

            // Actualizar estado de la solicitud si se especificó
            if ($validated['estado_resultante']) {
                $solicitud->update(['estado_gestion' => $validated['estado_resultante']]);
            }

            // Actualizar fecha de contacto
            if ($validated['proximo_contacto']) {
                $solicitud->update(['fecha_contacto' => $validated['proximo_contacto']]);
            } else {
                $solicitud->update(['fecha_contacto' => now()]);
            }

            // Subir archivos si existen
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/solicitudes-comerciales',
                            'resource_type' => 'auto',
                        ]);

                        SolicitudArchivo::create([
                            'solicitud_comercial_id' => $solicitud->id,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'ruta_archivo' => $result['secure_url'],
                            'tipo_mime' => $archivo->getMimeType(),
                            'tamaño' => $archivo->getSize(),
                            'cargado_por_user_id' => auth()->id(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de solicitud comercial a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => auth()->id(),
                'accion' => 'update',
                'modelo' => 'SolicitudComercial',
                'modelo_id' => $solicitud->id,
                'descripcion' => "Seguimiento agregado a solicitud comercial: {$solicitud->nombre_contacto}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            DB::commit();

            // Enviar correo al contacto de la solicitud
            try {
                if ($solicitud->email && filter_var($solicitud->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($solicitud->email)->send(new SeguimientoSolicitudComercial($seguimiento));
                } else {
                    \Log::warning("No se pudo enviar correo de seguimiento: email inválido para solicitud {$solicitud->id}");
                }
            } catch (\Exception $emailException) {
                // Log del error pero no fallar la creación del seguimiento
                \Log::error('Error al enviar email de seguimiento de solicitud comercial: ' . $emailException->getMessage());
            }

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $seguimiento->load('usuario'),
                    'message' => 'Seguimiento agregado exitosamente'
                ]);
            }

            return redirect()->route('superadmin.solicitudes-comerciales.show', $solicitud->id)
                ->with('success', 'Seguimiento agregado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al agregar seguimiento: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al agregar el seguimiento: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error al agregar el seguimiento: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar archivo de una solicitud comercial
     */
    public function eliminarArchivo($id, $archivoId)
    {
        $solicitud = SolicitudComercial::findOrFail($id);
        $archivo = SolicitudArchivo::where('solicitud_comercial_id', $solicitud->id)
            ->findOrFail($archivoId);

        try {
            // Eliminar de Cloudinary
            $urlParts = parse_url($archivo->ruta_archivo);
            $pathParts = explode('/', trim($urlParts['path'], '/'));
            $publicId = end($pathParts);
            $publicId = str_replace('.' . pathinfo($publicId, PATHINFO_EXTENSION), '', $publicId);
            
            Cloudinary::uploadApi()->destroy('domoph/solicitudes-comerciales/' . $publicId);
        } catch (\Exception $e) {
            \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
        }

        $archivo->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado exitosamente'
            ]);
        }

        return back()->with('success', 'Archivo eliminado exitosamente.');
    }
}
