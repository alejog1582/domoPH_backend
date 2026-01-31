<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comunicado;
use App\Models\Residente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComunicadoController extends Controller
{
    /**
     * Obtener comunicados de los últimos 6 meses
     */
    public function index(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar tu unidad o propiedad.',
                'error' => 'UNIT_NOT_FOUND'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;

        // Calcular fecha de hace 6 meses
        $fechaDesde = Carbon::now()->subMonths(6)->startOfDay();

        // Query base: comunicados de los últimos 6 meses
        $query = Comunicado::where('copropiedad_id', $propiedadId)
            ->where('publicado', true)
            ->where('activo', true)
            ->where(function($q) use ($fechaDesde) {
                $q->where('fecha_publicacion', '>=', $fechaDesde)
                  ->orWhereNull('fecha_publicacion'); // Incluir comunicados sin fecha de publicación
            })
            ->where(function($query) use ($residente) {
                // Comunicados visibles para todos
                $query->where('visible_para', 'todos')
                      // O visibles para propietarios (si el residente es propietario)
                      ->orWhere(function($q) use ($residente) {
                          if ($residente->tipo_relacion === 'propietario') {
                              $q->where('visible_para', 'propietarios');
                          }
                      })
                      // O visibles para arrendatarios (si el residente es arrendatario)
                      ->orWhere(function($q) use ($residente) {
                          if ($residente->tipo_relacion === 'arrendatario') {
                              $q->where('visible_para', 'arrendatarios');
                          }
                      })
                      // O comunicados específicos para esta unidad
                      ->orWhereHas('unidades', function($q) use ($residente) {
                          $q->where('unidades.id', $residente->unidad->id);
                      })
                      // O comunicados específicos para este residente
                      ->orWhereHas('residentes', function($q) use ($residente) {
                          $q->where('residentes.id', $residente->id);
                      });
            })
            ->orderBy('destacado', 'desc') // Destacados primero
            ->orderBy('fecha_publicacion', 'desc')
            ->orderBy('created_at', 'desc');

        $comunicados = $query->get()->map(function ($comunicado) use ($residente) {
            // Verificar si el residente ya leyó este comunicado
            $leido = DB::table('comunicado_residente')
                ->where('comunicado_id', $comunicado->id)
                ->where('residente_id', $residente->id)
                ->where('leido', true)
                ->exists();

            return [
                'id' => $comunicado->id,
                'titulo' => $comunicado->titulo,
                'slug' => $comunicado->slug,
                'resumen' => $comunicado->resumen,
                'contenido' => $comunicado->contenido,
                'tipo' => $comunicado->tipo,
                'destacado' => $comunicado->destacado ?? false,
                'imagen_portada' => $comunicado->imagen_portada,
                'fecha_publicacion' => $comunicado->fecha_publicacion 
                    ? $comunicado->fecha_publicacion->format('Y-m-d H:i:s')
                    : $comunicado->created_at->format('Y-m-d H:i:s'),
                'fecha_publicacion_formateada' => $comunicado->fecha_publicacion 
                    ? $comunicado->fecha_publicacion->format('d M Y')
                    : $comunicado->created_at->format('d M Y'),
                'visible_para' => $comunicado->visible_para,
                'leido' => $leido,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'comunicados' => $comunicados,
            ]
        ], 200);
    }

    /**
     * Obtener un comunicado específico por ID o slug
     */
    public function show(Request $request, $id)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar tu unidad o propiedad.',
                'error' => 'UNIT_NOT_FOUND'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;

        // Buscar por ID o slug
        $comunicado = Comunicado::where('copropiedad_id', $propiedadId)
            ->where(function($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('slug', $id);
            })
            ->where('publicado', true)
            ->where('activo', true)
            ->first();

        if (!$comunicado) {
            return response()->json([
                'success' => false,
                'message' => 'Comunicado no encontrado.',
                'error' => 'NOT_FOUND'
            ], 404);
        }

        // Verificar si el residente ya leyó este comunicado
        $leido = DB::table('comunicado_residente')
            ->where('comunicado_id', $comunicado->id)
            ->where('residente_id', $residente->id)
            ->where('leido', true)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $comunicado->id,
                'titulo' => $comunicado->titulo,
                'slug' => $comunicado->slug,
                'resumen' => $comunicado->resumen,
                'contenido' => $comunicado->contenido,
                'tipo' => $comunicado->tipo,
                'destacado' => $comunicado->destacado ?? false,
                'imagen_portada' => $comunicado->imagen_portada,
                'fecha_publicacion' => $comunicado->fecha_publicacion 
                    ? $comunicado->fecha_publicacion->format('Y-m-d H:i:s')
                    : $comunicado->created_at->format('Y-m-d H:i:s'),
                'fecha_publicacion_formateada' => $comunicado->fecha_publicacion 
                    ? $comunicado->fecha_publicacion->format('d M Y')
                    : $comunicado->created_at->format('d M Y'),
                'visible_para' => $comunicado->visible_para,
                'autor' => $comunicado->autor ? [
                    'id' => $comunicado->autor->id,
                    'nombre' => $comunicado->autor->nombre,
                ] : null,
                'leido' => $leido,
            ]
        ], 200);
    }

    /**
     * Marcar un comunicado como leído
     */
    public function marcarLeido(Request $request, $id)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar tu unidad o propiedad.',
                'error' => 'UNIT_NOT_FOUND'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;

        // Verificar que el comunicado existe y pertenece a la propiedad
        $comunicado = Comunicado::where('copropiedad_id', $propiedadId)
            ->where(function($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('slug', $id);
            })
            ->where('publicado', true)
            ->where('activo', true)
            ->first();

        if (!$comunicado) {
            return response()->json([
                'success' => false,
                'message' => 'Comunicado no encontrado.',
                'error' => 'NOT_FOUND'
            ], 404);
        }

        // Verificar si ya existe el registro
        $existe = DB::table('comunicado_residente')
            ->where('comunicado_id', $comunicado->id)
            ->where('residente_id', $residente->id)
            ->exists();

        if ($existe) {
            // Si ya existe, actualizar solo si no está marcado como leído
            $registro = DB::table('comunicado_residente')
                ->where('comunicado_id', $comunicado->id)
                ->where('residente_id', $residente->id)
                ->first();

            if (!$registro->leido) {
                DB::table('comunicado_residente')
                    ->where('comunicado_id', $comunicado->id)
                    ->where('residente_id', $residente->id)
                    ->update([
                        'leido' => true,
                        'fecha_lectura' => now(),
                        'updated_at' => now(),
                    ]);
            }
        } else {
            // Si no existe, crear el registro
            DB::table('comunicado_residente')->insert([
                'comunicado_id' => $comunicado->id,
                'residente_id' => $residente->id,
                'leido' => true,
                'fecha_lectura' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Comunicado marcado como leído.',
        ], 200);
    }
}
