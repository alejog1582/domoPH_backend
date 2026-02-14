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

        // Validar que se puedan actualizar configuraciones de tipo boolean, html, text o number
        if (!in_array($configuracion->tipo, ['boolean', 'html', 'text', 'number'])) {
            return back()->with('error', 'Solo se pueden actualizar configuraciones de tipo boolean, html, text o number.');
        }

        $valor = null;

        if ($configuracion->tipo === 'boolean') {
            // Obtener el valor (puede venir como string 'true'/'false', '1'/'0', o boolean)
            // Si no viene, significa que el checkbox está desmarcado (false)
            $valor = $request->has('valor') ? $request->input('valor') : false;
            
            if (is_string($valor)) {
                $valor = $valor === 'true' || $valor === '1';
            }
            
            // Validación especial para cobro_parq_visitantes
            if ($configuracion->clave === 'cobro_parq_visitantes' && $valor) {
                // Verificar que el valor por minuto sea mayor a 0
                $valorMinuto = DB::table('configuraciones_propiedad')
                    ->where('propiedad_id', $propiedad->id)
                    ->where('clave', 'valor_minuto_parq_visitantes')
                    ->value('valor');
                
                if (floatval($valorMinuto) <= 0) {
                    return back()->with('error', 'No se puede habilitar el cobro de parqueaderos de visitantes si el valor por minuto es menor o igual a 0.');
                }
            }
            
            $valor = $valor ? 'true' : 'false';
        } elseif (in_array($configuracion->tipo, ['html', 'text'])) {
            // Para configuraciones de tipo html o text
            $request->validate([
                'valor' => 'required|string',
            ]);
            $valor = $request->input('valor');
        } elseif ($configuracion->tipo === 'number') {
            // Para configuraciones de tipo number
            $request->validate([
                'valor' => 'required|numeric|min:0',
            ]);
            $valor = (string) $request->input('valor');
            
            // Si se actualiza valor_minuto_parq_visitantes y es 0 o menor, desactivar cobro_parq_visitantes
            if ($configuracion->clave === 'valor_minuto_parq_visitantes' && floatval($valor) <= 0) {
                DB::table('configuraciones_propiedad')
                    ->where('propiedad_id', $propiedad->id)
                    ->where('clave', 'cobro_parq_visitantes')
                    ->update([
                        'valor' => 'false',
                        'updated_at' => now(),
                    ]);
            }
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
            
            // Obtener los valores enviados para configuraciones number
            $valoresNumber = $request->input('configuraciones_number', []);

            foreach ($todasConfiguraciones as $configuracion) {
                if ($configuracion->tipo === 'boolean') {
                    // Si está en el array enviado, está marcado (true), si no, está desmarcado (false)
                    $estaMarcado = isset($valoresBoolean[$configuracion->id]);
                    
                    // Validación especial para cobro_parq_visitantes
                    if ($configuracion->clave === 'cobro_parq_visitantes' && $estaMarcado) {
                        // Verificar que el valor por minuto sea mayor a 0
                        $valorMinuto = DB::table('configuraciones_propiedad')
                            ->where('propiedad_id', $propiedad->id)
                            ->where('clave', 'valor_minuto_parq_visitantes')
                            ->value('valor');
                        
                        if (floatval($valorMinuto) <= 0) {
                            DB::rollBack();
                            return back()->with('error', 'No se puede habilitar el cobro de parqueaderos de visitantes si el valor por minuto es menor o igual a 0.');
                        }
                    }
                    
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
                } elseif ($configuracion->tipo === 'number' && isset($valoresNumber[$configuracion->id])) {
                    // Validar que sea numérico y mayor o igual a 0
                    $valorNumber = $valoresNumber[$configuracion->id];
                    
                    if (!is_numeric($valorNumber) || floatval($valorNumber) < 0) {
                        DB::rollBack();
                        return back()->with('error', "El valor para '{$configuracion->clave}' debe ser un número mayor o igual a 0.");
                    }
                    
                    // Si se actualiza valor_minuto_parq_visitantes y es 0 o menor, desactivar cobro_parq_visitantes
                    if ($configuracion->clave === 'valor_minuto_parq_visitantes' && floatval($valorNumber) <= 0) {
                        DB::table('configuraciones_propiedad')
                            ->where('propiedad_id', $propiedad->id)
                            ->where('clave', 'cobro_parq_visitantes')
                            ->update([
                                'valor' => 'false',
                                'updated_at' => now(),
                            ]);
                    }
                    
                    DB::table('configuraciones_propiedad')
                        ->where('id', $configuracion->id)
                        ->update([
                            'valor' => (string) $valorNumber,
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
