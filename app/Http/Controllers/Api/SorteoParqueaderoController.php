<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SorteoParqueadero;
use App\Models\ParticipanteSorteoParqueadero;
use App\Models\Residente;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SorteoParqueaderoController extends Controller
{
    /**
     * Obtener información del sorteo activo para el residente
     */
    public function getSorteoActivo()
    {
        $user = Auth::user();
        
        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente) {
            $residente = Residente::where('user_id', $user->id)
                ->activos()
                ->with(['unidad.propiedad'])
                ->first();
        }

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información del residente o propiedad.'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;

        // Buscar sorteo activo
        $sorteoActivo = SorteoParqueadero::where('copropiedad_id', $propiedadId)
            ->where('estado', 'activo')
            ->where('activo', true)
            ->whereDate('fecha_inicio_recoleccion', '<=', Carbon::now())
            ->whereDate('fecha_fin_recoleccion', '>=', Carbon::now())
            ->first();

        if (!$sorteoActivo) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un sorteo de parqueaderos activo en este momento.'
            ], 404);
        }

        // Verificar si el residente ya está inscrito
        $participaciones = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $sorteoActivo->id)
            ->where('residente_id', $residente->id)
            ->where('activo', true)
            ->get();

        $inscritoCarro = $participaciones->where('tipo_vehiculo', 'carro')->first();
        $inscritoMoto = $participaciones->where('tipo_vehiculo', 'moto')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'sorteo' => [
                    'id' => $sorteoActivo->id,
                    'titulo' => $sorteoActivo->titulo,
                    'descripcion' => $sorteoActivo->descripcion,
                    'fecha_inicio_recoleccion' => $sorteoActivo->fecha_inicio_recoleccion->format('Y-m-d'),
                    'fecha_fin_recoleccion' => $sorteoActivo->fecha_fin_recoleccion->format('Y-m-d'),
                    'fecha_sorteo' => $sorteoActivo->fecha_sorteo->format('Y-m-d'),
                    'fecha_inicio_uso' => $sorteoActivo->fecha_inicio_uso ? $sorteoActivo->fecha_inicio_uso->format('Y-m-d') : null,
                    'duracion_meses' => $sorteoActivo->duracion_meses,
                    'capacidad_autos' => $sorteoActivo->capacidad_autos,
                    'capacidad_motos' => $sorteoActivo->capacidad_motos,
                ],
                'inscripciones' => [
                    'carro' => $inscritoCarro ? [
                        'id' => $inscritoCarro->id,
                        'placa' => $inscritoCarro->placa,
                        'fecha_inscripcion' => $inscritoCarro->fecha_inscripcion->format('Y-m-d H:i:s'),
                        'parqueadero_asignado' => $inscritoCarro->parqueadero_asignado,
                        'fue_favorecido' => $inscritoCarro->fue_favorecido,
                    ] : null,
                    'moto' => $inscritoMoto ? [
                        'id' => $inscritoMoto->id,
                        'placa' => $inscritoMoto->placa,
                        'fecha_inscripcion' => $inscritoMoto->fecha_inscripcion->format('Y-m-d H:i:s'),
                        'parqueadero_asignado' => $inscritoMoto->parqueadero_asignado,
                        'fue_favorecido' => $inscritoMoto->fue_favorecido,
                    ] : null,
                ],
            ]
        ], 200);
    }

    /**
     * Inscribirse al sorteo de parqueaderos
     */
    public function inscribirse(Request $request)
    {
        $user = Auth::user();
        
        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente) {
            $residente = Residente::where('user_id', $user->id)
                ->activos()
                ->with(['unidad.propiedad'])
                ->first();
        }

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información del residente o propiedad.'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;

        // Validar datos de entrada
        $validated = $request->validate([
            'sorteo_id' => 'required|exists:sorteos_parqueadero,id',
            'tipo_vehiculo' => 'required|in:carro,moto',
            'placa' => 'required|string|max:10',
            'tarjeta_propiedad' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB máximo
            'soat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tecnomecanica' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'sorteo_id.required' => 'El ID del sorteo es obligatorio.',
            'sorteo_id.exists' => 'El sorteo especificado no existe.',
            'tipo_vehiculo.required' => 'El tipo de vehículo es obligatorio.',
            'tipo_vehiculo.in' => 'El tipo de vehículo debe ser carro o moto.',
            'placa.required' => 'La placa es obligatoria.',
            'placa.max' => 'La placa no puede exceder 10 caracteres.',
            'tarjeta_propiedad.required' => 'La tarjeta de propiedad es obligatoria.',
            'tarjeta_propiedad.file' => 'La tarjeta de propiedad debe ser un archivo.',
            'tarjeta_propiedad.mimes' => 'La tarjeta de propiedad debe ser PDF, JPG, JPEG o PNG.',
            'tarjeta_propiedad.max' => 'La tarjeta de propiedad no debe exceder 5MB.',
            'soat.required' => 'El SOAT es obligatorio.',
            'soat.file' => 'El SOAT debe ser un archivo.',
            'soat.mimes' => 'El SOAT debe ser PDF, JPG, JPEG o PNG.',
            'soat.max' => 'El SOAT no debe exceder 5MB.',
            'tecnomecanica.file' => 'La tecnomecánica debe ser un archivo.',
            'tecnomecanica.mimes' => 'La tecnomecánica debe ser PDF, JPG, JPEG o PNG.',
            'tecnomecanica.max' => 'La tecnomecánica no debe exceder 5MB.',
        ]);

        // Verificar que el sorteo existe y está activo
        $sorteo = SorteoParqueadero::where('id', $validated['sorteo_id'])
            ->where('copropiedad_id', $propiedadId)
            ->where('estado', 'activo')
            ->where('activo', true)
            ->whereDate('fecha_inicio_recoleccion', '<=', Carbon::now())
            ->whereDate('fecha_fin_recoleccion', '>=', Carbon::now())
            ->first();

        if (!$sorteo) {
            return response()->json([
                'success' => false,
                'message' => 'El sorteo no está disponible para inscripción en este momento.'
            ], 400);
        }

        // Verificar que el residente no esté ya inscrito con este tipo de vehículo
        $yaInscrito = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $sorteo->id)
            ->where('residente_id', $residente->id)
            ->where('tipo_vehiculo', $validated['tipo_vehiculo'])
            ->where('activo', true)
            ->exists();

        if ($yaInscrito) {
            return response()->json([
                'success' => false,
                'message' => "Ya estás inscrito en este sorteo con un vehículo tipo {$validated['tipo_vehiculo']}."
            ], 400);
        }

        try {
            // Subir archivos a Cloudinary
            $tarjetaPropiedadUrl = null;
            $soatUrl = null;
            $tecnomecanicaUrl = null;

            // Subir tarjeta de propiedad
            if ($request->hasFile('tarjeta_propiedad')) {
                $result = Cloudinary::uploadApi()->upload(
                    $request->file('tarjeta_propiedad')->getRealPath(),
                    [
                        'folder' => 'sorteos_parqueadero/tarjetas_propiedad',
                        'resource_type' => 'auto',
                    ]
                );
                $tarjetaPropiedadUrl = $result['secure_url'];
            }

            // Subir SOAT
            if ($request->hasFile('soat')) {
                $result = Cloudinary::uploadApi()->upload(
                    $request->file('soat')->getRealPath(),
                    [
                        'folder' => 'sorteos_parqueadero/soat',
                        'resource_type' => 'auto',
                    ]
                );
                $soatUrl = $result['secure_url'];
            }

            // Subir tecnomecánica (opcional)
            if ($request->hasFile('tecnomecanica')) {
                $result = Cloudinary::uploadApi()->upload(
                    $request->file('tecnomecanica')->getRealPath(),
                    [
                        'folder' => 'sorteos_parqueadero/tecnomecanica',
                        'resource_type' => 'auto',
                    ]
                );
                $tecnomecanicaUrl = $result['secure_url'];
            }

            // Crear la inscripción
            $participante = ParticipanteSorteoParqueadero::create([
                'sorteo_parqueadero_id' => $sorteo->id,
                'copropiedad_id' => $propiedadId,
                'unidad_id' => $residente->unidad->id,
                'residente_id' => $residente->id,
                'tipo_vehiculo' => $validated['tipo_vehiculo'],
                'placa' => strtoupper($validated['placa']),
                'tarjeta_propiedad_url' => $tarjetaPropiedadUrl,
                'soat_url' => $soatUrl,
                'tecnomecanica_url' => $tecnomecanicaUrl,
                'fecha_inscripcion' => Carbon::now(),
                'activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inscripción realizada exitosamente.',
                'data' => [
                    'participante' => [
                        'id' => $participante->id,
                        'placa' => $participante->placa,
                        'tipo_vehiculo' => $participante->tipo_vehiculo,
                        'fecha_inscripcion' => $participante->fecha_inscripcion->format('Y-m-d H:i:s'),
                    ],
                    'sorteo' => [
                        'fecha_sorteo' => $sorteo->fecha_sorteo->format('Y-m-d'),
                        'fecha_sorteo_formateada' => $sorteo->fecha_sorteo->format('d/m/Y'),
                    ],
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al inscribirse al sorteo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la inscripción. Por favor, intenta nuevamente.'
            ], 500);
        }
    }
}
