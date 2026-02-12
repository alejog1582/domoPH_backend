@extends('layouts.app')

@section('title', 'Solicitudes Comerciales - SuperAdmin')

@section('content')
<div class="mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Solicitudes Comerciales</h1>
        <p class="mt-2 text-sm text-gray-600">Gestiona las solicitudes comerciales de la plataforma</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('superadmin.solicitudes-comerciales.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Buscar por nombre, empresa, email..." 
                class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <select name="tipo_solicitud" class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los tipos</option>
                <option value="cotizacion" {{ request('tipo_solicitud') == 'cotizacion' ? 'selected' : '' }}>Cotización</option>
                <option value="demo" {{ request('tipo_solicitud') == 'demo' ? 'selected' : '' }}>Demo</option>
                <option value="contacto" {{ request('tipo_solicitud') == 'contacto' ? 'selected' : '' }}>Contacto</option>
            </select>
            <select name="estado_gestion" class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="pendiente" {{ request('estado_gestion') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="en_proceso" {{ request('estado_gestion') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                <option value="contactado" {{ request('estado_gestion') == 'contactado' ? 'selected' : '' }}>Contactado</option>
                <option value="cerrado_ganado" {{ request('estado_gestion') == 'cerrado_ganado' ? 'selected' : '' }}>Cerrado - Ganado</option>
                <option value="cerrado_perdido" {{ request('estado_gestion') == 'cerrado_perdido' ? 'selected' : '' }}>Cerrado - Perdido</option>
            </select>
            <select name="prioridad" class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todas las prioridades</option>
                <option value="baja" {{ request('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                <option value="media" {{ request('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                <option value="alta" {{ request('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
            </select>
            <button type="submit" class="btn-gradient-primary">
                <i class="fas fa-search mr-2"></i> Buscar
            </button>
        </form>
    </div>

    <table class="table-domoph min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($solicitudes as $solicitud)
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $solicitud->nombre_contacto }}</div>
                        <div class="text-sm text-gray-500">{{ $solicitud->empresa ?? 'Sin empresa' }}</div>
                        <div class="text-xs text-gray-400">{{ $solicitud->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($solicitud->tipo_solicitud) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoColors = [
                                'pendiente' => 'bg-yellow-100 text-yellow-800',
                                'en_proceso' => 'bg-blue-100 text-blue-800',
                                'contactado' => 'bg-purple-100 text-purple-800',
                                'cerrado_ganado' => 'bg-green-100 text-green-800',
                                'cerrado_perdido' => 'bg-red-100 text-red-800',
                            ];
                            $estadoLabels = [
                                'pendiente' => 'Pendiente',
                                'en_proceso' => 'En Proceso',
                                'contactado' => 'Contactado',
                                'cerrado_ganado' => 'Ganado',
                                'cerrado_perdido' => 'Perdido',
                            ];
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $estadoColors[$solicitud->estado_gestion] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $estadoLabels[$solicitud->estado_gestion] ?? $solicitud->estado_gestion }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $prioridadColors = [
                                'baja' => 'bg-gray-100 text-gray-800',
                                'media' => 'bg-yellow-100 text-yellow-800',
                                'alta' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $prioridadColors[$solicitud->prioridad] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($solicitud->prioridad) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $solicitud->asignadoA->nombre ?? 'Sin asignar' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $solicitud->created_at->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $solicitud->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="abrirModalGestionar({{ $solicitud->id }})" class="text-green-600 hover:text-green-900 mr-3" title="Gestionar">
                            <i class="fas fa-tasks"></i>
                        </button>
                        <a href="{{ route('superadmin.solicitudes-comerciales.show', $solicitud) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron solicitudes comerciales
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $solicitudes->links() }}
    </div>
</div>

<!-- Modal para Gestionar Solicitud -->
<div id="modalGestionar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Gestionar Solicitud Comercial</h3>
                <button onclick="cerrarModalGestionar()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="formGestionar" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="solicitud_id" name="solicitud_id">
                
                <div class="space-y-4">
                    <!-- Comentario del seguimiento -->
                    <div>
                        <label for="comentario" class="block text-sm font-medium text-gray-700 mb-1">
                            Comentario / Nota del seguimiento <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="comentario" 
                            name="comentario" 
                            rows="4" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Describe el seguimiento realizado..."
                        ></textarea>
                    </div>
                    
                    <!-- Estado resultante -->
                    <div>
                        <label for="estado_resultante" class="block text-sm font-medium text-gray-700 mb-1">
                            Cambiar estado a (opcional)
                        </label>
                        <select 
                            id="estado_resultante" 
                            name="estado_resultante"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Mantener estado actual</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="contactado">Contactado</option>
                            <option value="cerrado_ganado">Cerrado - Ganado</option>
                            <option value="cerrado_perdido">Cerrado - Perdido</option>
                        </select>
                    </div>
                    
                    <!-- Próximo contacto -->
                    <div>
                        <label for="proximo_contacto" class="block text-sm font-medium text-gray-700 mb-1">
                            Próximo contacto (opcional)
                        </label>
                        <input 
                            type="datetime-local" 
                            id="proximo_contacto" 
                            name="proximo_contacto"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    
                    <!-- Archivos -->
                    <div>
                        <label for="archivos" class="block text-sm font-medium text-gray-700 mb-1">
                            Archivos adjuntos (opcional)
                        </label>
                        <input 
                            type="file" 
                            id="archivos" 
                            name="archivos[]" 
                            multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        <p class="mt-1 text-xs text-gray-500">Puedes seleccionar múltiples archivos. Tamaño máximo: 10MB por archivo.</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="cerrarModalGestionar()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-save mr-2"></i> Guardar Seguimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function abrirModalGestionar(solicitudId) {
        document.getElementById('solicitud_id').value = solicitudId;
        document.getElementById('formGestionar').action = `/superadmin/solicitudes-comerciales/${solicitudId}/seguimientos`;
        document.getElementById('modalGestionar').classList.remove('hidden');
    }
    
    function cerrarModalGestionar() {
        document.getElementById('modalGestionar').classList.add('hidden');
        document.getElementById('formGestionar').reset();
    }
    
    // Cerrar modal al hacer clic fuera de él
    document.getElementById('modalGestionar').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalGestionar();
        }
    });
</script>
@endpush
@endsection
