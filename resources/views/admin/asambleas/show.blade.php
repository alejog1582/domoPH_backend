@extends('admin.layouts.app')

@section('title', 'Detalle de Asamblea')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.asambleas.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Asambleas
    </a>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Asamblea</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $asamblea->titulo }}</p>
        </div>
        <div class="flex items-center space-x-3">
            @if($asamblea->estado == 'programada' && \App\Helpers\AdminHelper::hasPermission('asambleas.edit'))
            <a href="{{ route('admin.asambleas.edit', $asamblea->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            @endif
            @if($asamblea->estado == 'programada' && \App\Helpers\AdminHelper::hasPermission('asambleas.delete'))
            <form action="{{ route('admin.asambleas.destroy', $asamblea->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta asamblea?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>
                    Eliminar
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
@endif

<!-- Información de la Asamblea -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Asamblea</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">Título</label>
            <p class="text-lg font-semibold text-gray-900">{{ $asamblea->titulo }}</p>
        </div>

        @if($asamblea->descripcion)
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">Descripción</label>
            <p class="text-gray-900 whitespace-pre-wrap">{{ $asamblea->descripcion }}</p>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Tipo</label>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $asamblea->tipo == 'ordinaria' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                {{ ucfirst($asamblea->tipo) }}
            </span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Modalidad</label>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                {{ ucfirst($asamblea->modalidad) }}
            </span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Inicio</label>
            <p class="text-gray-900">{{ \Carbon\Carbon::parse($asamblea->fecha_inicio)->format('d/m/Y H:i') }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Fin</label>
            <p class="text-gray-900">{{ \Carbon\Carbon::parse($asamblea->fecha_fin)->format('d/m/Y H:i') }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
            @php
                $estadoColors = [
                    'programada' => 'bg-yellow-100 text-yellow-800',
                    'en_curso' => 'bg-blue-100 text-blue-800',
                    'finalizada' => 'bg-green-100 text-green-800',
                    'cancelada' => 'bg-red-100 text-red-800',
                ];
                $estadoColor = $estadoColors[$asamblea->estado] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $estadoColor }}">
                {{ ucfirst(str_replace('_', ' ', $asamblea->estado)) }}
            </span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Quorum</label>
            <p class="text-gray-900">
                Mínimo: <span class="font-semibold">{{ number_format($asamblea->quorum_minimo, 2) }}%</span>
                @if($asamblea->quorum_actual !== null)
                <br>Actual: <span class="font-semibold">{{ number_format($asamblea->quorum_actual, 2) }}%</span>
                @endif
            </p>
        </div>

        @if($asamblea->url_transmision)
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">URL de Transmisión</label>
            <a href="{{ $asamblea->url_transmision }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                {{ $asamblea->url_transmision }}
                <i class="fas fa-external-link-alt ml-1"></i>
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Documentos -->
@if($documentos->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Documentos Soportes</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visible Para</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($documentos as $documento)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $documento->nombre }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ ucfirst(str_replace('_', ' ', $documento->tipo)) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ ucfirst($documento->visible_para) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ $documento->archivo_url }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Ver
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Asistencias -->
@if($asistencias->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Asistencias</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Presente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coeficiente Voto</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($asistencias as $asistencia)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $asistencia->residente_nombre ?? $asistencia->user_nombre ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ ucfirst($asistencia->rol) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($asistencia->presente)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Sí</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">No</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ number_format($asistencia->coeficiente_voto, 4) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Votaciones -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Votaciones</h2>
        @if(\App\Helpers\AdminHelper::hasPermission('asambleas.votaciones'))
        <button type="button" onclick="toggleModalVotacion()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Votación
        </button>
        @endif
    </div>
    
    @if($votaciones->count() > 0)
    <div class="space-y-6">
        @foreach($votaciones as $votacion)
        <div class="border border-gray-300 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $votacion->titulo }}</h3>
                    @if($votacion->descripcion)
                    <p class="text-sm text-gray-600 mt-1">{{ $votacion->descripcion }}</p>
                    @endif
                </div>
                <div>
                    @if($votacion->estado == 'abierta')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Abierta</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Cerrada</span>
                    @endif
                </div>
            </div>
            
            <div class="mb-3 text-sm text-gray-600">
                <i class="fas fa-calendar mr-1"></i>
                Inicio: {{ \Carbon\Carbon::parse($votacion->fecha_inicio)->format('d/m/Y H:i') }}
                @if($votacion->fecha_fin)
                <br><i class="fas fa-calendar-times mr-1"></i>
                Fin: {{ \Carbon\Carbon::parse($votacion->fecha_fin)->format('d/m/Y H:i') }}
                @endif
            </div>

            <!-- Resultados de la Votación -->
            <div class="mt-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Resultados:</h4>
                <div class="space-y-2">
                    @foreach($votacion->opciones as $opcion)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span class="text-sm text-gray-900">{{ $opcion->opcion }}</span>
                        <div class="text-right">
                            <span class="text-sm font-semibold text-gray-900">{{ $opcion->votos }} votos</span>
                            @if($votacion->total_votos > 0)
                            <span class="text-xs text-gray-500 ml-2">
                                ({{ number_format(($opcion->votos / $votacion->total_votos) * 100, 2) }}%)
                            </span>
                            @endif
                            @if($opcion->coeficiente_total > 0)
                            <div class="text-xs text-gray-500">
                                Coef. Total: {{ number_format($opcion->coeficiente_total, 4) }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    <strong>Total de votos:</strong> {{ $votacion->total_votos }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-gray-500 text-center py-8">No hay votaciones registradas</p>
    @endif
</div>

<!-- Modal para Crear Votación -->
@if(\App\Helpers\AdminHelper::hasPermission('asambleas.votaciones'))
<div id="modalVotacion" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Crear Votación</h3>
            <button type="button" onclick="toggleModalVotacion()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.asambleas.store-votacion', $asamblea->id) }}" method="POST" id="formVotacion">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label for="votacion_titulo" class="block text-sm font-medium text-gray-700 mb-1">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="titulo" id="votacion_titulo" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Ej: Aprobación del presupuesto 2026">
                </div>

                <div>
                    <label for="votacion_descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                        Descripción
                    </label>
                    <textarea name="descripcion" id="votacion_descripcion" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Descripción de la votación..."></textarea>
                </div>

                <div>
                    <label for="votacion_tipo" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo" id="votacion_tipo" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione un tipo</option>
                        <option value="si_no">Sí / No</option>
                        <option value="opcion_multiple">Opción Múltiple</option>
                    </select>
                </div>

                <div>
                    <label for="votacion_fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha y Hora de Inicio <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="fecha_inicio" id="votacion_fecha_inicio" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="votacion_fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha y Hora de Fin
                    </label>
                    <input type="datetime-local" name="fecha_fin" id="votacion_fecha_fin"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div id="opciones-container">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Opciones <span class="text-red-500">*</span>
                    </label>
                    <div id="opciones-list" class="space-y-2">
                        <input type="text" name="opciones[]" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Opción 1">
                        <input type="text" name="opciones[]" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Opción 2">
                    </div>
                    <button type="button" onclick="agregarOpcion()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-plus mr-1"></i>Agregar Opción
                    </button>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end space-x-3">
                <button type="button" onclick="toggleModalVotacion()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Crear Votación
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function toggleModalVotacion() {
    const modal = document.getElementById('modalVotacion');
    modal.classList.toggle('hidden');
}

function agregarOpcion() {
    const container = document.getElementById('opciones-list');
    const nuevaOpcion = document.createElement('input');
    nuevaOpcion.type = 'text';
    nuevaOpcion.name = 'opciones[]';
    nuevaOpcion.required = true;
    nuevaOpcion.className = 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-2';
    nuevaOpcion.placeholder = 'Nueva opción';
    container.appendChild(nuevaOpcion);
}

// Validar fecha_fin > fecha_inicio para votación
document.addEventListener('DOMContentLoaded', function() {
    const fechaInicioVotacion = document.getElementById('votacion_fecha_inicio');
    const fechaFinVotacion = document.getElementById('votacion_fecha_fin');

    if (fechaInicioVotacion && fechaFinVotacion) {
        fechaInicioVotacion.addEventListener('change', function() {
            fechaFinVotacion.min = this.value;
        });

        fechaFinVotacion.addEventListener('change', function() {
            if (fechaInicioVotacion.value && this.value <= fechaInicioVotacion.value) {
                alert('La fecha de fin debe ser mayor que la fecha de inicio.');
                this.value = '';
            }
        });
    }
});
</script>
@endsection
