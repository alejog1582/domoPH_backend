@extends('admin.layouts.app')

@section('title', 'Encuestas y Votaciones - Administrador')

@section('content')
<!-- Mensajes Flash -->
@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times cursor-pointer"></i>
        </span>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times cursor-pointer"></i>
        </span>
    </div>
@endif

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Encuestas y Votaciones</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de encuestas y votaciones de la copropiedad</p>
        </div>
        <div class="flex gap-2">
            @if(\App\Helpers\AdminHelper::hasPermission('encuestas.create'))
            <a href="{{ route('admin.encuestas-votaciones.create', ['tipo' => 'encuesta']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Encuesta
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('votaciones.create'))
            <a href="{{ route('admin.encuestas-votaciones.create', ['tipo' => 'votacion']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Votación
            </a>
            @endif
        </div>
    </div>
</div>

@if($propiedad)
    <!-- Pestañas -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'todos']) }}" 
                   class="{{ $tipo == 'todos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Todos
                </a>
                <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'encuestas']) }}" 
                   class="{{ $tipo == 'encuestas' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Encuestas
                </a>
                <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'votaciones']) }}" 
                   class="{{ $tipo == 'votaciones' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Votaciones
                </a>
            </nav>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <form method="GET" action="{{ route('admin.encuestas-votaciones.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="tipo" value="{{ $tipo }}">
            
            <!-- Búsqueda -->
            <div>
                <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                    placeholder="Título, descripción..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Estado -->
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="estado" id="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                    <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                    <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
            </div>

            <!-- Botones -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
                <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => $tipo]) }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Encuestas -->
    @if(($tipo == 'encuestas' || $tipo == 'todos') && $encuestas->count() > 0)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                <i class="fas fa-clipboard-list mr-2"></i>Encuestas
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Respuestas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($encuestas as $encuesta)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $encuesta->titulo }}</div>
                            @if($encuesta->descripcion)
                            <div class="text-sm text-gray-500">{{ Str::limit($encuesta->descripcion, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $encuesta->tipo_respuesta == 'opcion_multiple' ? 'Opción Múltiple' : 'Respuesta Abierta' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $encuesta->fecha_inicio->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $encuesta->fecha_fin->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $estadoColors = [
                                    'activa' => 'bg-green-100 text-green-800',
                                    'cerrada' => 'bg-gray-100 text-gray-800',
                                    'anulada' => 'bg-red-100 text-red-800',
                                ];
                                $color = $estadoColors[$encuesta->estado] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                {{ ucfirst($encuesta->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $encuesta->respuestas_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                @if(\App\Helpers\AdminHelper::hasPermission('encuestas.respuestas'))
                                <a href="{{ route('admin.encuestas-votaciones.show', ['id' => $encuesta->id, 'tipo' => 'encuesta']) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="Ver Respuestas">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                @if(\App\Helpers\AdminHelper::hasPermission('encuestas.edit'))
                                <a href="{{ route('admin.encuestas-votaciones.edit', ['id' => $encuesta->id, 'tipo' => 'encuesta']) }}" 
                                   class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if(\App\Helpers\AdminHelper::hasPermission('encuestas.delete'))
                                <button onclick="confirmarEliminacion({{ $encuesta->id }}, 'encuesta', '{{ $encuesta->titulo }}')" 
                                        class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Votaciones -->
    @if(($tipo == 'votaciones' || $tipo == 'todos') && $votaciones->count() > 0)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                <i class="fas fa-vote-yea mr-2"></i>Votaciones
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Votos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($votaciones as $votacion)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $votacion->titulo }}</div>
                            @if($votacion->descripcion)
                            <div class="text-sm text-gray-500">{{ Str::limit($votacion->descripcion, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $votacion->fecha_inicio->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $votacion->fecha_fin->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $estadoColors = [
                                    'activa' => 'bg-green-100 text-green-800',
                                    'cerrada' => 'bg-gray-100 text-gray-800',
                                    'anulada' => 'bg-red-100 text-red-800',
                                ];
                                $color = $estadoColors[$votacion->estado] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                {{ ucfirst($votacion->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $votacion->votos_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                @if(\App\Helpers\AdminHelper::hasPermission('votaciones.resultados'))
                                <a href="{{ route('admin.encuestas-votaciones.show', ['id' => $votacion->id, 'tipo' => 'votacion']) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="Ver Resultados">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                @endif
                                @if(\App\Helpers\AdminHelper::hasPermission('votaciones.edit'))
                                <a href="{{ route('admin.encuestas-votaciones.edit', ['id' => $votacion->id, 'tipo' => 'votacion']) }}" 
                                   class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if(\App\Helpers\AdminHelper::hasPermission('votaciones.delete'))
                                <button onclick="confirmarEliminacion({{ $votacion->id }}, 'votacion', '{{ $votacion->titulo }}')" 
                                        class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(($tipo == 'encuestas' || $tipo == 'todos') && $encuestas->count() == 0 && ($tipo == 'votaciones' || $tipo == 'todos') && $votaciones->count() == 0)
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
        <p class="text-gray-500">No hay encuestas o votaciones registradas.</p>
    </div>
    @endif

@else
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>No hay propiedad asignada. Contacta al superadministrador.</span>
        </div>
    </div>
@endif

<!-- Modal de Confirmación de Eliminación -->
<div id="modalEliminar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-5">¿Está seguro?</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    ¿Está seguro de que desea eliminar <strong id="itemNombre"></strong>? Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="flex items-center justify-center space-x-4 px-4 py-3 mt-4">
                <button onclick="cerrarModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
                <form id="formEliminar" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="tipo" id="tipoEliminar">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmarEliminacion(id, tipo, nombre) {
        document.getElementById('itemNombre').textContent = nombre;
        document.getElementById('tipoEliminar').value = tipo;
        document.getElementById('formEliminar').action = '{{ route("admin.encuestas-votaciones.destroy", ":id") }}'.replace(':id', id) + '?tipo=' + tipo;
        document.getElementById('modalEliminar').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalEliminar').classList.add('hidden');
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modalEliminar');
        if (event.target == modal) {
            cerrarModal();
        }
    }
</script>
@endpush
@endsection
