@extends('admin.layouts.app')

@section('title', 'Asambleas')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Asambleas</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de asambleas de copropietarios (últimos 3 años)</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('asambleas.create'))
        <a href="{{ route('admin.asambleas.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Asamblea
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
    <form method="GET" action="{{ route('admin.asambleas.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="estado" id="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="programada" {{ request('estado') == 'programada' ? 'selected' : '' }}>Programada</option>
                    <option value="en_curso" {{ request('estado') == 'en_curso' ? 'selected' : '' }}>En Curso</option>
                    <option value="finalizada" {{ request('estado') == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                    <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select name="tipo" id="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="ordinaria" {{ request('tipo') == 'ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                    <option value="extraordinaria" {{ request('tipo') == 'extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                </select>
            </div>
            <div>
                <label for="modalidad" class="block text-sm font-medium text-gray-700 mb-2">Modalidad</label>
                <select name="modalidad" id="modalidad" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todas</option>
                    <option value="presencial" {{ request('modalidad') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                    <option value="virtual" {{ request('modalidad') == 'virtual' ? 'selected' : '' }}>Virtual</option>
                    <option value="mixta" {{ request('modalidad') == 'mixta' ? 'selected' : '' }}>Mixta</option>
                </select>
            </div>
            <div>
                <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
        </div>
        <div class="mt-4 flex space-x-4">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
            <a href="{{ route('admin.asambleas.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                <i class="fas fa-redo mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    @if($asambleas->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modalidad</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quorum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($asambleas as $asamblea)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $asamblea->titulo }}</div>
                        @if($asamblea->descripcion)
                        <div class="text-sm text-gray-500 mt-1">{{ Str::limit($asamblea->descripcion, 60) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $asamblea->tipo == 'ordinaria' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($asamblea->tipo) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ ucfirst($asamblea->modalidad) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($asamblea->fecha_inicio)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>Mínimo: {{ number_format($asamblea->quorum_minimo, 2) }}%</div>
                        @if($asamblea->quorum_actual !== null)
                        <div class="text-xs">Actual: {{ number_format($asamblea->quorum_actual, 2) }}%</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoColors = [
                                'programada' => 'bg-yellow-100 text-yellow-800',
                                'en_curso' => 'bg-blue-100 text-blue-800',
                                'finalizada' => 'bg-green-100 text-green-800',
                                'cancelada' => 'bg-red-100 text-red-800',
                            ];
                            $estadoColor = $estadoColors[$asamblea->estado] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $estadoColor }}">
                            {{ ucfirst(str_replace('_', ' ', $asamblea->estado)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.asambleas.show', $asamblea->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($asamblea->estado == 'programada' && \App\Helpers\AdminHelper::hasPermission('asambleas.edit'))
                            <a href="{{ route('admin.asambleas.edit', $asamblea->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            @if($asamblea->estado == 'programada' && \App\Helpers\AdminHelper::hasPermission('asambleas.delete'))
                            <form action="{{ route('admin.asambleas.destroy', $asamblea->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta asamblea?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $asambleas->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <i class="fas fa-calendar-times text-gray-400 text-6xl mb-4"></i>
        <p class="text-gray-500 text-lg">No hay asambleas registradas en los últimos 3 años</p>
        @if(\App\Helpers\AdminHelper::hasPermission('asambleas.create'))
        <a href="{{ route('admin.asambleas.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Primera Asamblea
        </a>
        @endif
    </div>
    @endif
</div>
@endsection
