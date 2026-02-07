@extends('admin.layouts.app')

@section('title', 'Tareas y Seguimiento')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Tareas y Seguimiento</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de tareas del Consejo de Administración</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('consejo-tareas.create'))
        <a href="{{ route('admin.consejo-tareas.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Tarea
        </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
@endif

<!-- Filtros -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" action="{{ route('admin.consejo-tareas.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="estado" id="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Pendiente y En Progreso</option>
                    <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_progreso" {{ request('estado') == 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                    <option value="bloqueada" {{ request('estado') == 'bloqueada' ? 'selected' : '' }}>Bloqueada</option>
                    <option value="finalizada" {{ request('estado') == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                </select>
            </div>
            <div>
                <label for="acta_id" class="block text-sm font-medium text-gray-700 mb-2">Acta</label>
                <select name="acta_id" id="acta_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todas las actas</option>
                    @foreach($actas as $acta)
                    <option value="{{ $acta->id }}" {{ request('acta_id') == $acta->id ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($acta->fecha_acta)->format('d/m/Y') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="responsable_id" class="block text-sm font-medium text-gray-700 mb-2">Responsable</label>
                <select name="responsable_id" id="responsable_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    @foreach($integrantes as $integrante)
                    <option value="{{ $integrante->id }}" {{ request('responsable_id') == $integrante->id ? 'selected' : '' }}>
                        {{ $integrante->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="prioridad" class="block text-sm font-medium text-gray-700 mb-2">Prioridad</label>
                <select name="prioridad" id="prioridad" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todas</option>
                    <option value="baja" {{ request('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                    <option value="media" {{ request('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                    <option value="alta" {{ request('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                </select>
            </div>
            <div>
                <label for="decision_id" class="block text-sm font-medium text-gray-700 mb-2">Decisión</label>
                <input type="text" name="decision_id" id="decision_id" value="{{ request('decision_id') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="ID de decisión">
            </div>
        </div>
        <div class="mt-4 flex space-x-4">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
            <a href="{{ route('admin.consejo-tareas.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                <i class="fas fa-redo mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responsable</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Vencimiento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($tareas as $tarea)
            @php
                $responsable = $tarea->responsable_id ? \Illuminate\Support\Facades\DB::table('consejo_integrantes')->where('id', $tarea->responsable_id)->first() : null;
            @endphp
            <tr>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $tarea->titulo }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ Str::limit($tarea->descripcion, 60) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $responsable ? $responsable->nombre : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($tarea->prioridad == 'alta')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Alta</span>
                    @elseif($tarea->prioridad == 'media')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Media</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Baja</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $tarea->fecha_vencimiento ? \Carbon\Carbon::parse($tarea->fecha_vencimiento)->format('d/m/Y') : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($tarea->estado == 'pendiente')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Pendiente</span>
                    @elseif($tarea->estado == 'en_progreso')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">En Progreso</span>
                    @elseif($tarea->estado == 'bloqueada')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Bloqueada</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Finalizada</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        @if(\App\Helpers\AdminHelper::hasPermission('consejo-tareas.view'))
                        <a href="{{ route('admin.consejo-tareas.show', $tarea->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endif
                        @if($tarea->puede_editar && \App\Helpers\AdminHelper::hasPermission('consejo-tareas.edit'))
                        <a href="{{ route('admin.consejo-tareas.edit', $tarea->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        @if($tarea->puede_eliminar && \App\Helpers\AdminHelper::hasPermission('consejo-tareas.delete'))
                        <form action="{{ route('admin.consejo-tareas.destroy', $tarea->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta tarea?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                        @if(\App\Helpers\AdminHelper::hasPermission('consejo-tareas.seguimiento'))
                        <a href="{{ route('admin.consejo-tareas.gestionar', $tarea->id) }}" class="text-green-600 hover:text-green-900" title="Gestionar">
                            <i class="fas fa-tasks"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay tareas registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tareas->hasPages())
<div class="mt-4">{{ $tareas->links() }}</div>
@endif
@endsection
