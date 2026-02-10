@extends('layouts.app')

@section('title', 'Detalle de Solicitud Comercial - SuperAdmin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="{{ route('superadmin.solicitudes-comerciales.index') }}" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Detalle de Solicitud Comercial</h1>
        <p class="mt-2 text-sm text-gray-600">Información completa de la solicitud</p>
    </div>
    <button onclick="abrirModalGestionar({{ $solicitud->id }})" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        <i class="fas fa-tasks mr-2"></i> Gestionar Solicitud
    </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Información Principal -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Datos del Contacto -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Información del Contacto</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nombre</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $solicitud->nombre_contacto }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $solicitud->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Teléfono</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $solicitud->telefono }}</p>
                </div>
                @if($solicitud->empresa)
                <div>
                    <label class="block text-sm font-medium text-gray-500">Empresa</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $solicitud->empresa }}</p>
                </div>
                @endif
                @if($solicitud->ciudad)
                <div>
                    <label class="block text-sm font-medium text-gray-500">Ciudad</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $solicitud->ciudad }}</p>
                </div>
                @endif
                @if($solicitud->pais)
                <div>
                    <label class="block text-sm font-medium text-gray-500">País</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $solicitud->pais }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Mensaje -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Mensaje</h2>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $solicitud->mensaje }}</p>
        </div>

        <!-- Seguimientos -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Historial de Seguimientos</h2>
            @if($solicitud->seguimientos->count() > 0)
                <div class="space-y-4">
                    @foreach($solicitud->seguimientos->sortByDesc('created_at') as $seguimiento)
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $seguimiento->usuario->nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ $seguimiento->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                @if($seguimiento->estado_resultante)
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
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $estadoColors[$seguimiento->estado_resultante] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $estadoLabels[$seguimiento->estado_resultante] ?? $seguimiento->estado_resultante }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $seguimiento->comentario }}</p>
                            @if($seguimiento->proximo_contacto)
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Próximo contacto: {{ \Carbon\Carbon::parse($seguimiento->proximo_contacto)->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No hay seguimientos registrados aún.</p>
            @endif
        </div>

        <!-- Archivos -->
        @if($solicitud->archivos->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Archivos Adjuntos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($solicitud->archivos as $archivo)
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $archivo->nombre_archivo }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $archivo->tamaño ? number_format($archivo->tamaño / 1024, 2) . ' KB' : 'N/A' }}
                                    @if($archivo->cargadoPor)
                                        - Cargado por: {{ $archivo->cargadoPor->nombre }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ $archivo->ruta_archivo }}" target="_blank" class="text-blue-600 hover:text-blue-900 ml-4" title="Ver archivo">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar - Información de Gestión -->
    <div class="space-y-6">
        <!-- Estado y Prioridad -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Estado de Gestión</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Tipo de Solicitud</label>
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ ucfirst($solicitud->tipo_solicitud) }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Estado</label>
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
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $estadoColors[$solicitud->estado_gestion] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $estadoLabels[$solicitud->estado_gestion] ?? $solicitud->estado_gestion }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Prioridad</label>
                    @php
                        $prioridadColors = [
                            'baja' => 'bg-gray-100 text-gray-800',
                            'media' => 'bg-yellow-100 text-yellow-800',
                            'alta' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $prioridadColors[$solicitud->prioridad] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($solicitud->prioridad) }}
                    </span>
                </div>
                @if($solicitud->asignadoA)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Asignado a</label>
                    <p class="text-sm text-gray-900">{{ $solicitud->asignadoA->nombre }}</p>
                </div>
                @endif
                @if($solicitud->origen)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Origen</label>
                    <p class="text-sm text-gray-900">{{ ucfirst($solicitud->origen) }}</p>
                </div>
                @endif
                @if($solicitud->fecha_contacto)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Último contacto</label>
                    <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($solicitud->fecha_contacto)->format('d/m/Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Información de Fechas -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Información de Fechas</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Fecha de creación</label>
                    <p class="text-sm text-gray-900">{{ $solicitud->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Última actualización</label>
                    <p class="text-sm text-gray-900">{{ $solicitud->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Gestionar Solicitud (mismo que en index) -->
<div id="modalGestionar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Gestionar Solicitud Comercial</h3>
                <button onclick="cerrarModalGestionar()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="formGestionar" method="POST" enctype="multipart/form-data" action="">
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
    let solicitudIdActual = null;
    
    function abrirModalGestionar(solicitudId) {
        solicitudIdActual = solicitudId;
        document.getElementById('solicitud_id').value = solicitudId;
        document.getElementById('modalGestionar').classList.remove('hidden');
    }
    
    function cerrarModalGestionar() {
        document.getElementById('modalGestionar').classList.add('hidden');
        document.getElementById('formGestionar').reset();
        solicitudIdActual = null;
    }
    
    // Cerrar modal al hacer clic fuera de él
    document.getElementById('modalGestionar').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalGestionar();
        }
    });
    
    // Manejar envío del formulario con AJAX
    document.getElementById('formGestionar').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!solicitudIdActual) {
            alert('Error: No se ha seleccionado una solicitud válida.');
            return;
        }
        
        const form = this;
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        // Deshabilitar botón y mostrar loading
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';
        
        // Obtener token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                         document.querySelector('input[name="_token"]')?.value;
        
        if (!csrfToken) {
            alert('Error: Token de seguridad no encontrado. Por favor, recarga la página.');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            return;
        }
        
        // Agregar token CSRF si no está presente
        if (!formData.has('_token')) {
            formData.append('_token', csrfToken);
        }
        
        // Enviar petición AJAX
        fetch(`/superadmin/solicitudes-comerciales/${solicitudIdActual}/seguimientos`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (response.status === 419) {
                throw new Error('La sesión ha expirado. Por favor, recarga la página e intenta nuevamente.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Mostrar mensaje de éxito
                alert('Seguimiento guardado exitosamente.');
                // Recargar la página para mostrar el nuevo seguimiento
                window.location.reload();
            } else {
                throw new Error(data.message || 'Error al guardar el seguimiento.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Error al guardar el seguimiento. Por favor, intenta nuevamente.');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
</script>
@endpush
@endsection
