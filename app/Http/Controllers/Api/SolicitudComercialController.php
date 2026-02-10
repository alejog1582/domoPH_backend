<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NuevaSolicitudComercial;
use App\Models\SolicitudComercial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SolicitudComercialController extends Controller
{
    /**
     * Crear una nueva solicitud comercial desde el frontend público
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_solicitud' => 'required|in:cotizacion,demo,contacto',
            'nombre_contacto' => 'required|string|max:200',
            'empresa' => 'nullable|string|max:200',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:50',
            'ciudad' => 'nullable|string|max:100',
            'pais' => 'nullable|string|max:100',
            'mensaje' => 'required|string',
            'origen' => 'nullable|in:landing,web,whatsapp,referido,otro',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $solicitud = SolicitudComercial::create([
                'tipo_solicitud' => $request->tipo_solicitud,
                'nombre_contacto' => $request->nombre_contacto,
                'empresa' => $request->empresa ?? null,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'ciudad' => $request->ciudad ?? null,
                'pais' => $request->pais ?? null,
                'mensaje' => $request->mensaje,
                'origen' => $request->origen ?? 'web',
                'estado_gestion' => 'pendiente',
                'prioridad' => 'media',
                'activo' => true,
            ]);

            // Enviar email al SuperAdmin
            try {
                $emailSuperAdmin = env('EMAIL_SUPERADMIN');
                if ($emailSuperAdmin) {
                    Mail::to($emailSuperAdmin)->send(new NuevaSolicitudComercial($solicitud));
                } else {
                    \Log::warning('EMAIL_SUPERADMIN no está configurado en el archivo .env');
                }
            } catch (\Exception $emailException) {
                // Log del error pero no fallar la creación de la solicitud
                \Log::error('Error al enviar email de notificación de solicitud comercial: ' . $emailException->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud enviada exitosamente. Nos pondremos en contacto contigo pronto.',
                'data' => [
                    'id' => $solicitud->id,
                ]
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear solicitud comercial desde API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la solicitud. Por favor, intenta nuevamente.'
            ], 500);
        }
    }
}
