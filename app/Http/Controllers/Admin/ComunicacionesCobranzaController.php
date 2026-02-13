<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComunicacionesCobranzaController extends Controller
{
    /**
     * Mostrar la lista de comunicaciones de cobranza
     */
    public function index()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicaciones = DB::table('comunicaciones_cobranza')
            ->where('copropiedad_id', $propiedad->id)
            ->whereNull('deleted_at')
            ->orderBy('dia_envio_mes', 'asc')
            ->orderBy('dias_mora_desde', 'asc')
            ->get();

        return view('admin.comunicaciones-cobranza.index', compact('comunicaciones', 'propiedad'));
    }

    /**
     * Mostrar el formulario de creación
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.comunicaciones-cobranza.create', compact('propiedad'));
    }

    /**
     * Guardar una nueva comunicación
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
            'canal' => 'required|in:email,whatsapp,ambos',
            'dia_envio_mes' => 'required|integer|min:1|max:31',
            'dias_mora_desde' => 'required|integer|min:0',
            'dias_mora_hasta' => 'nullable|integer|min:0',
            'asunto' => 'nullable|string|max:255',
            'mensaje_email' => 'nullable|string',
            'mensaje_whatsapp' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'canal.required' => 'El canal es obligatorio.',
            'canal.in' => 'El canal debe ser email, whatsapp o ambos.',
            'dia_envio_mes.required' => 'El día de envío es obligatorio.',
            'dia_envio_mes.min' => 'El día de envío debe ser entre 1 y 31.',
            'dia_envio_mes.max' => 'El día de envío debe ser entre 1 y 31.',
            'dias_mora_desde.required' => 'Los días de mora desde son obligatorios.',
            'dias_mora_desde.min' => 'Los días de mora desde deben ser mayor o igual a 0.',
        ]);

        // Validar que dias_mora_hasta sea mayor que dias_mora_desde si está presente
        if ($request->filled('dias_mora_hasta') && $request->dias_mora_hasta < $request->dias_mora_desde) {
            return back()->withErrors(['dias_mora_hasta' => 'Los días de mora hasta deben ser mayores o iguales a los días de mora desde.'])->withInput();
        }

        DB::table('comunicaciones_cobranza')->insert([
            'copropiedad_id' => $propiedad->id,
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'canal' => $validated['canal'],
            'dia_envio_mes' => $validated['dia_envio_mes'],
            'dias_mora_desde' => $validated['dias_mora_desde'],
            'dias_mora_hasta' => $validated['dias_mora_hasta'] ?? null,
            'asunto' => $validated['asunto'] ?? null,
            'mensaje_email' => $validated['mensaje_email'] ?? null,
            'mensaje_whatsapp' => $validated['mensaje_whatsapp'] ?? null,
            'activo' => $request->has('activo') ? true : false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.comunicaciones-cobranza.index')
            ->with('success', 'Comunicación de cobranza creada exitosamente.');
    }

    /**
     * Mostrar el formulario de edición
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('comunicaciones_cobranza')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.comunicaciones-cobranza.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        return view('admin.comunicaciones-cobranza.edit', compact('comunicacion', 'propiedad'));
    }

    /**
     * Actualizar una comunicación
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('comunicaciones_cobranza')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.comunicaciones-cobranza.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'canal' => 'required|in:email,whatsapp,ambos',
            'dia_envio_mes' => 'required|integer|min:1|max:31',
            'dias_mora_desde' => 'required|integer|min:0',
            'dias_mora_hasta' => 'nullable|integer|min:0',
            'asunto' => 'nullable|string|max:255',
            'mensaje_email' => 'nullable|string',
            'mensaje_whatsapp' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'canal.required' => 'El canal es obligatorio.',
            'canal.in' => 'El canal debe ser email, whatsapp o ambos.',
            'dia_envio_mes.required' => 'El día de envío es obligatorio.',
            'dia_envio_mes.min' => 'El día de envío debe ser entre 1 y 31.',
            'dia_envio_mes.max' => 'El día de envío debe ser entre 1 y 31.',
            'dias_mora_desde.required' => 'Los días de mora desde son obligatorios.',
            'dias_mora_desde.min' => 'Los días de mora desde deben ser mayor o igual a 0.',
        ]);

        // Validar que dias_mora_hasta sea mayor que dias_mora_desde si está presente
        if ($request->filled('dias_mora_hasta') && $request->dias_mora_hasta < $request->dias_mora_desde) {
            return back()->withErrors(['dias_mora_hasta' => 'Los días de mora hasta deben ser mayores o iguales a los días de mora desde.'])->withInput();
        }

        DB::table('comunicaciones_cobranza')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'canal' => $validated['canal'],
                'dia_envio_mes' => $validated['dia_envio_mes'],
                'dias_mora_desde' => $validated['dias_mora_desde'],
                'dias_mora_hasta' => $validated['dias_mora_hasta'] ?? null,
                'asunto' => $validated['asunto'] ?? null,
                'mensaje_email' => $validated['mensaje_email'] ?? null,
                'mensaje_whatsapp' => $validated['mensaje_whatsapp'] ?? null,
                'activo' => $request->has('activo') ? true : false,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.comunicaciones-cobranza.index')
            ->with('success', 'Comunicación de cobranza actualizada exitosamente.');
    }

    /**
     * Eliminar una comunicación (soft delete)
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('comunicaciones_cobranza')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.comunicaciones-cobranza.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        DB::table('comunicaciones_cobranza')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.comunicaciones-cobranza.index')
            ->with('success', 'Comunicación de cobranza eliminada exitosamente.');
    }

    /**
     * Previsualizar cómo se vería la comunicación para un residente
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('comunicaciones_cobranza')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.comunicaciones-cobranza.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        // Obtener un residente de ejemplo para la previsualización
        $residenteEjemplo = DB::table('residentes')
            ->join('users', 'residentes.user_id', '=', 'users.id')
            ->join('unidades', 'residentes.unidad_id', '=', 'unidades.id')
            ->where('unidades.propiedad_id', $propiedad->id)
            ->where('residentes.es_principal', true)
            ->select('users.nombre as nombre_residente', 'unidades.numero as unidad')
            ->first();

        // Reemplazar variables dinámicas en los mensajes
        $mensajeEmailPreview = $comunicacion->mensaje_email;
        $mensajeWhatsappPreview = $comunicacion->mensaje_whatsapp;

        if ($residenteEjemplo) {
            $mensajeEmailPreview = str_replace('{nombre_residente}', $residenteEjemplo->nombre_residente, $mensajeEmailPreview ?? '');
            $mensajeEmailPreview = str_replace('{unidad}', $residenteEjemplo->unidad, $mensajeEmailPreview);
            $mensajeEmailPreview = str_replace('{saldo}', '$850.000', $mensajeEmailPreview);
            $mensajeEmailPreview = str_replace('{fecha_vencimiento}', now()->format('d/m/Y'), $mensajeEmailPreview);
            $mensajeEmailPreview = str_replace('{copropiedad}', $propiedad->nombre, $mensajeEmailPreview);

            $mensajeWhatsappPreview = str_replace('{nombre_residente}', $residenteEjemplo->nombre_residente, $mensajeWhatsappPreview ?? '');
            $mensajeWhatsappPreview = str_replace('{unidad}', $residenteEjemplo->unidad, $mensajeWhatsappPreview);
            $mensajeWhatsappPreview = str_replace('{saldo}', '$850.000', $mensajeWhatsappPreview);
            $mensajeWhatsappPreview = str_replace('{fecha_vencimiento}', now()->format('d/m/Y'), $mensajeWhatsappPreview);
            $mensajeWhatsappPreview = str_replace('{copropiedad}', $propiedad->nombre, $mensajeWhatsappPreview);
        }

        return view('admin.comunicaciones-cobranza.show', compact('comunicacion', 'propiedad', 'mensajeEmailPreview', 'mensajeWhatsappPreview', 'residenteEjemplo'));
    }
}
