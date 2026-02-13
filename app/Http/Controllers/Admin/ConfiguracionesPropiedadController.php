<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionesPropiedadController extends Controller
{
    /**
     * Mostrar la lista de configuraciones de la propiedad
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener todas las configuraciones de la propiedad
        $configuraciones = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $propiedad->id)
            ->orderBy('clave', 'asc')
            ->get();

        return view('admin.configuraciones-propiedad.index', compact('configuraciones', 'propiedad'));
    }

    /**
     * Actualizar una configuración
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Validar que la configuración pertenezca a la propiedad
        $configuracion = DB::table('configuraciones_propiedad')
            ->where('id', $id)
            ->where('propiedad_id', $propiedad->id)
            ->first();

        if (!$configuracion) {
            return back()->with('error', 'Configuración no encontrada.');
        }

        // Validar que se puedan actualizar configuraciones de tipo boolean o html
        if (!in_array($configuracion->tipo, ['boolean', 'html', 'text'])) {
            return back()->with('error', 'Solo se pueden actualizar configuraciones de tipo boolean, html o text.');
        }

        $valor = null;

        if ($configuracion->tipo === 'boolean') {
            // Obtener el valor (puede venir como string 'true'/'false', '1'/'0', o boolean)
            // Si no viene, significa que el checkbox está desmarcado (false)
            $valor = $request->has('valor') ? $request->input('valor') : false;
            
            if (is_string($valor)) {
                $valor = $valor === 'true' || $valor === '1';
            }
            $valor = $valor ? 'true' : 'false';
        } elseif (in_array($configuracion->tipo, ['html', 'text'])) {
            // Para configuraciones de tipo html o text
            $request->validate([
                'valor' => 'required|string',
            ]);
            $valor = $request->input('valor');
        }
        
        // Actualizar la configuración
        DB::table('configuraciones_propiedad')
            ->where('id', $id)
            ->update([
                'valor' => $valor,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Configuración actualizada exitosamente.');
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public function updateMultiple(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener todas las configuraciones de la propiedad
        $todasConfiguraciones = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $propiedad->id)
            ->get();

        DB::beginTransaction();
        try {
            // Obtener los valores enviados para configuraciones boolean (solo los checkboxes marcados se envían)
            $valoresBoolean = $request->input('configuraciones', []);
            
            // Obtener los valores enviados para configuraciones html/text
            $valoresHtml = $request->input('configuraciones_html', []);

            foreach ($todasConfiguraciones as $configuracion) {
                if ($configuracion->tipo === 'boolean') {
                    // Si está en el array enviado, está marcado (true), si no, está desmarcado (false)
                    $estaMarcado = isset($valoresBoolean[$configuracion->id]);
                    
                    // Convertir a string 'true' o 'false'
                    $valorFinal = $estaMarcado ? 'true' : 'false';
                    
                    DB::table('configuraciones_propiedad')
                        ->where('id', $configuracion->id)
                        ->update([
                            'valor' => $valorFinal,
                            'updated_at' => now(),
                        ]);
                } elseif (in_array($configuracion->tipo, ['html', 'text']) && isset($valoresHtml[$configuracion->id])) {
                    // Actualizar configuraciones de tipo html o text
                    DB::table('configuraciones_propiedad')
                        ->where('id', $configuracion->id)
                        ->update([
                            'valor' => $valoresHtml[$configuracion->id],
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Configuraciones actualizadas exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar configuraciones: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar las configuraciones: ' . $e->getMessage());
        }
    }
}
