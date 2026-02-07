<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Propiedad;
use App\Models\Licitacion;
use App\Models\OfertaLicitacion;
use App\Models\OfertaArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Carbon\Carbon;

class LicitacionPublicaController extends Controller
{
    /**
     * Mostrar licitaciones públicas de una propiedad
     */
    public function index($propiedad_id)
    {
        $propiedad = Propiedad::findOrFail($propiedad_id);

        $licitaciones = Licitacion::where('copropiedad_id', $propiedad->id)
            ->where('visible_publicamente', true)
            ->where('activo', true)
            ->where('estado', 'publicada')
            ->whereDate('fecha_cierre', '>=', Carbon::now())
            ->with('archivos')
            ->orderBy('fecha_publicacion', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('publico.licitaciones.index', compact('propiedad', 'licitaciones'));
    }

    /**
     * Mostrar detalles de una licitación pública
     */
    public function show($id)
    {
        $licitacion = Licitacion::where('visible_publicamente', true)
            ->where('activo', true)
            ->where('estado', 'publicada')
            ->with(['archivos', 'copropiedad'])
            ->findOrFail($id);

        return view('publico.licitaciones.show', compact('licitacion'));
    }

    /**
     * Mostrar formulario para crear oferta
     */
    public function createOferta($id)
    {
        $licitacion = Licitacion::where('visible_publicamente', true)
            ->where('activo', true)
            ->where('estado', 'publicada')
            ->whereDate('fecha_cierre', '>=', Carbon::now())
            ->with('copropiedad')
            ->findOrFail($id);

        return view('publico.licitaciones.create-oferta', compact('licitacion'));
    }

    /**
     * Guardar oferta de proveedor
     */
    public function storeOferta(Request $request, $id)
    {
        $licitacion = Licitacion::where('visible_publicamente', true)
            ->where('activo', true)
            ->where('estado', 'publicada')
            ->whereDate('fecha_cierre', '>=', Carbon::now())
            ->findOrFail($id);

        $validated = $request->validate([
            'nombre_proveedor' => 'required|string|max:255',
            'nit_proveedor' => 'nullable|string|max:50',
            'email_contacto' => 'required|email|max:255',
            'telefono_contacto' => 'nullable|string|max:50',
            'descripcion_oferta' => 'required|string',
            'valor_ofertado' => 'required|numeric|min:0',
            'archivos.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $oferta = OfertaLicitacion::create([
                'licitacion_id' => $licitacion->id,
                'nombre_proveedor' => $validated['nombre_proveedor'],
                'nit_proveedor' => $validated['nit_proveedor'] ?? null,
                'email_contacto' => $validated['email_contacto'],
                'telefono_contacto' => $validated['telefono_contacto'] ?? null,
                'descripcion_oferta' => $validated['descripcion_oferta'],
                'valor_ofertado' => $validated['valor_ofertado'],
                'estado' => 'recibida',
                'fecha_postulacion' => Carbon::now(),
                'es_ganadora' => false,
            ]);

            // Subir archivos a Cloudinary
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/ofertas/' . $licitacion->copropiedad_id . '/' . $licitacion->id,
                            'resource_type' => 'auto',
                        ]);

                        OfertaArchivo::create([
                            'oferta_licitacion_id' => $oferta->id,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'url_archivo' => $result['secure_url'] ?? $result['url'] ?? null,
                            'tipo_archivo' => $archivo->getMimeType(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de oferta a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('licitaciones-publicas.show', $licitacion->id)
                ->with('success', 'Tu oferta ha sido enviada exitosamente. Será revisada por el administrador.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear oferta: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al enviar la oferta. Por favor, intente nuevamente.');
        }
    }
}
