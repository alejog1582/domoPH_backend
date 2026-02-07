@extends('admin.layouts.app')

@section('title', 'Decisiones del Consejo')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Decisiones del Consejo</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de decisiones tomadas en las reuniones del Consejo</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('consejo-decisiones.create'))
        <a href="{{ route('admin.consejo-decisiones.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Decisión
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
    <form method="GET" action="{{ route('admin.consejo-decisiones.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="estado" id="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="cumplida" {{ request('estado') == 'cumplida' ? 'selected' : '' }}>Cumplida</option>
                </select>
            </div>
            <div>
                <label for="acta_id" class="block text-sm font-medium text-gray-700 mb-2">Acta</label>
                <select name="acta_id" id="acta_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todas las actas</option>
                    @foreach($actas as $acta)
                    <option value="{{ $acta->id }}" {{ request('acta_id') == $acta->id ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($acta->fecha_acta)->format('d/m/Y') }} - {{ ucfirst($acta->tipo_reunion) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 flex space-x-4">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
            <a href="{{ route('admin.consejo-decisiones.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acta</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responsable</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Compromiso</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($decisiones as $decision)
            <tr>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 max-w-md">
                        {{ Str::limit($decision->descripcion, 100) }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($decision->fecha_acta)->format('d/m/Y') }}
                    </div>
                    <div class="text-xs text-gray-500">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decision->tipo_reunion == 'ordinaria' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($decision->tipo_reunion) }}
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $decision->responsable ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $decision->fecha_compromiso ? \Carbon\Carbon::parse($decision->fecha_compromiso)->format('d/m/Y') : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($decision->estado == 'pendiente')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>
                    @elseif($decision->estado == 'en_proceso')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">En Proceso</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Cumplida</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        @if(\App\Helpers\AdminHelper::hasPermission('consejo-decisiones.view'))
                        <a href="{{ route('admin.consejo-decisiones.show', $decision->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endif
                        @if($decision->puede_editar && \App\Helpers\AdminHelper::hasPermission('consejo-decisiones.edit'))
                        <a href="{{ route('admin.consejo-decisiones.edit', $decision->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        @if($decision->puede_eliminar && \App\Helpers\AdminHelper::hasPermission('consejo-decisiones.delete'))
                        <form action="{{ route('admin.consejo-decisiones.destroy', $decision->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta decisión?');">
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
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay decisiones registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($decisiones->hasPages())
<div class="mt-4">{{ $decisiones->links() }}</div>
@endif
@endsection
