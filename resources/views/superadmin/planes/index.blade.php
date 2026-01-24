@extends('layouts.app')

@section('title', 'Planes - SuperAdmin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Planes</h1>
        <p class="mt-2 text-sm text-gray-600">Gestiona los planes disponibles del sistema</p>
    </div>
    <a href="{{ route('superadmin.planes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        <i class="fas fa-plus mr-2"></i> Nuevo Plan
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('superadmin.planes.index') }}" class="flex gap-4">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Buscar por nombre o slug..." 
                class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <select name="activo" class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activos</option>
                <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivos</option>
            </select>
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-search mr-2"></i> Buscar
            </button>
        </form>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Mensual</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Anual</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Límites</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($planes as $plan)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $plan->nombre }}</div>
                        <div class="text-xs text-gray-500">{{ $plan->slug }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${{ number_format($plan->precio_mensual, 0, ',', '.') }} COP</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            @if($plan->precio_anual)
                                ${{ number_format($plan->precio_anual, 0, ',', '.') }} COP
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500">
                            @if($plan->max_unidades)
                                <div>Unidades: {{ $plan->max_unidades }}</div>
                            @else
                                <div>Unidades: <span class="text-green-600">Ilimitado</span></div>
                            @endif
                            @if($plan->max_usuarios)
                                <div>Usuarios: {{ $plan->max_usuarios }}</div>
                            @else
                                <div>Usuarios: <span class="text-green-600">Ilimitado</span></div>
                            @endif
                            @if($plan->max_almacenamiento_mb)
                                <div>Almacenamiento: {{ number_format($plan->max_almacenamiento_mb / 1024, 1) }} GB</div>
                            @else
                                <div>Almacenamiento: <span class="text-green-600">Ilimitado</span></div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500">
                            {{ $plan->modulos->count() }} módulo(s)
                            @if($plan->caracteristicas && isset($plan->caracteristicas['modulos']))
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ count($plan->caracteristicas['modulos']) }} incluidos
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $plan->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                        @if($plan->soporte_prioritario)
                            <div class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Soporte Premium
                                </span>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('superadmin.planes.edit', ['plan' => $plan->id]) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="{{ route('superadmin.planes.destroy', ['plan' => $plan->id]) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este plan?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron planes
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
