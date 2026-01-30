<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Unidad;
use App\Models\ZonaSocial;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservaController extends Controller
{
    /**
     * Mostrar la lista de reservas
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Query base: reservas con sus relaciones
        $query = Reserva::with(['unidad', 'residente', 'zonaSocial', 'aprobadaPor', 'invitados'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('activo', true);

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado !== 'todos') {
                $query->where('estado', $request->estado);
            }
        }

        // Filtro por estado de pago
        if ($request->filled('estado_pago')) {
            if ($request->estado_pago !== 'todos') {
                $query->where('estado_pago', $request->estado_pago);
            }
        }

        // Filtro por zona social
        if ($request->filled('zona_social_id')) {
            $query->where('zona_social_id', $request->zona_social_id);
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        // Filtro por búsqueda de unidad
        if ($request->filled('buscar_unidad')) {
            $buscar = $request->buscar_unidad;
            $query->whereHas('unidad', function($q) use ($buscar) {
                $q->where('numero', 'like', "%{$buscar}%")
                  ->orWhere('torre', 'like', "%{$buscar}%")
                  ->orWhere('bloque', 'like', "%{$buscar}%");
            });
        }

        // Filtro por búsqueda en nombre solicitante
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('email_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('telefono_solicitante', 'like', "%{$buscar}%");
            });
        }

        // Filtro por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_reserva', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_reserva', '<=', $request->fecha_hasta);
        }

        // Ordenar por fecha de reserva (más recientes primero)
        $query->orderBy('fecha_reserva', 'desc')
              ->orderBy('hora_inicio', 'desc');

        // Paginación
        $reservas = $query->paginate(20)->withQueryString();

        // Obtener datos para filtros
        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get();

        $zonasSociales = ZonaSocial::where('propiedad_id', $propiedad->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('admin.reservas.index', compact('reservas', 'unidades', 'zonasSociales'));
    }

    /**
     * Mostrar el detalle de una reserva
     */
    public function show(Reserva $reserva)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad || $reserva->copropiedad_id !== $propiedad->id) {
            return redirect()->route('admin.reservas.index')
                ->with('error', 'Reserva no encontrada.');
        }

        // Cargar todas las relaciones necesarias
        $reserva->load([
            'unidad',
            'residente.user',
            'zonaSocial.horarios',
            'zonaSocial.imagenes',
            'aprobadaPor',
            'invitados'
        ]);

        return view('admin.reservas.show', compact('reserva'));
    }

    /**
     * Actualizar el estado de una reserva
     */
    public function update(Request $request, Reserva $reserva)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad || $reserva->copropiedad_id !== $propiedad->id) {
            return redirect()->route('admin.reservas.index')
                ->with('error', 'Reserva no encontrada.');
        }

        $request->validate([
            'estado' => 'required|in:solicitada,aprobada,rechazada,cancelada,finalizada',
            'estado_pago' => 'nullable|in:pendiente,pagado,exento,reembolsado',
            'motivo_rechazo' => 'nullable|string|max:500',
            'motivo_cancelacion' => 'nullable|string|max:500',
            'observaciones_admin' => 'nullable|string|max:1000',
            'incumplimiento' => 'nullable|boolean',
        ]);

        $estadoAnterior = $reserva->estado;
        $estadoNuevo = $request->estado;

        // Actualizar la reserva
        $reserva->update([
            'estado' => $estadoNuevo,
            'estado_pago' => $request->estado_pago ?? $reserva->estado_pago,
            'motivo_rechazo' => $request->motivo_rechazo,
            'motivo_cancelacion' => $request->motivo_cancelacion,
            'observaciones_admin' => $request->observaciones_admin,
            'incumplimiento' => $request->incumplimiento ?? false,
            'aprobada_por' => ($estadoNuevo === 'aprobada' || $estadoNuevo === 'rechazada') ? Auth::id() : $reserva->aprobada_por,
            'fecha_aprobacion' => ($estadoNuevo === 'aprobada' || $estadoNuevo === 'rechazada') ? now() : $reserva->fecha_aprobacion,
        ]);

        // Registrar en historial si cambió el estado
        if ($estadoAnterior !== $estadoNuevo) {
            \App\Models\ReservaHistorial::create([
                'reserva_id' => $reserva->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'comentario' => $request->observaciones_admin ?? 'Cambio de estado',
                'cambiado_por' => Auth::id(),
                'fecha_cambio' => now(),
            ]);
        }

        return redirect()->route('admin.reservas.show', $reserva)
            ->with('success', 'Reserva actualizada exitosamente.');
    }
}
