<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LogAuditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    /**
     * Listar logs de auditoría
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = LogAuditoria::with(['user', 'propiedad']);

        // Filtros
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('propiedad_id')) {
            $query->where('propiedad_id', $request->propiedad_id);
        }

        if ($request->has('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->has('modelo')) {
            $query->where('modelo', $request->modelo);
        }

        if ($request->has('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $query->orderBy('created_at', 'desc');

        // Paginación
        $perPage = $request->get('per_page', 50);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
            'message' => 'Logs de auditoría obtenidos exitosamente'
        ]);
    }
}
