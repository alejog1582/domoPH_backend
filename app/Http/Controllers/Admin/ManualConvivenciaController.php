<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualConvivencia;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ManualConvivenciaController extends Controller
{
    /**
     * Mostrar o crear el manual de convivencia
     */
    public function index()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Buscar el manual activo de la copropiedad
        $manual = ManualConvivencia::where('copropiedad_id', $propiedad->id)
            ->where('activo', true)
            ->first();

        return view('admin.manual-convivencia.index', compact('manual', 'propiedad'));
    }

    /**
     * Guardar o actualizar el manual de convivencia
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Validar datos
        $validated = $request->validate([
            'manual_url' => 'nullable|string|max:500',
            'manual_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB máximo
            'principales_deberes' => 'nullable|string',
            'principales_obligaciones' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'manual_file.file' => 'El archivo debe ser válido.',
            'manual_file.mimes' => 'El archivo debe ser un PDF.',
            'manual_file.max' => 'El archivo no debe exceder 10MB.',
        ]);

        try {
            // Buscar si existe un manual activo
            $manual = ManualConvivencia::where('copropiedad_id', $propiedad->id)
                ->where('activo', true)
                ->first();

            // Si no existe, crear uno nuevo
            if (!$manual) {
                $manual = new ManualConvivencia();
                $manual->copropiedad_id = $propiedad->id;
            }

            // Procesar archivo PDF si se proporciona
            if ($request->hasFile('manual_file')) {
                $archivo = $request->file('manual_file');
                $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                    'folder' => 'manuales_convivencia',
                    'resource_type' => 'auto',
                ]);
                $manual->manual_url = $result['secure_url'];
            } elseif ($request->filled('manual_url')) {
                // Si se proporciona una URL directamente (para mantener la existente)
                $manual->manual_url = $validated['manual_url'];
            } elseif (!$manual->manual_url) {
                // Si no hay archivo nuevo ni existente, dejar null
                $manual->manual_url = null;
            }

            // Actualizar contenido HTML
            $manual->principales_deberes = $validated['principales_deberes'] ?? null;
            $manual->principales_obligaciones = $validated['principales_obligaciones'] ?? null;
            $manual->activo = $request->has('activo') && $request->activo == '1';
            
            $manual->save();

            return redirect()->route('admin.manual-convivencia.index')
                ->with('success', 'Manual de convivencia guardado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error al guardar manual de convivencia: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar el manual de convivencia: ' . $e->getMessage());
        }
    }
}
