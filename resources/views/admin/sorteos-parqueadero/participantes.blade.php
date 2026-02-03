@extends('admin.layouts.app')

@section('title', 'Participantes del Sorteo - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Participantes del Sorteo</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $sorteo->titulo }}</p>
        </div>
        <a href="{{ route('admin.sorteos-parqueadero.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Sorteos
        </a>
    </div>
</div>

<!-- Información del Sorteo -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p class="text-sm text-gray-500">Estado</p>
            <p class="text-lg font-semibold">
                <span class="px-2 py-1 text-xs font-semibold rounded-full
                    @if($sorteo->estado == 'activo') bg-green-100 text-green-800
                    @elseif($sorteo->estado == 'cerrado') bg-gray-100 text-gray-800
                    @elseif($sorteo->estado == 'anulado') bg-red-100 text-red-800
                    @else bg-yellow-100 text-yellow-800
                    @endif">
                    {{ ucfirst($sorteo->estado) }}
                </span>
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Fecha Sorteo</p>
            <p class="text-lg font-semibold">{{ $sorteo->fecha_sorteo->format('d/m/Y') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Participantes</p>
            <p class="text-lg font-semibold">{{ $participantes->total() }}</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.sorteos-parqueadero.participantes', $sorteo->id) }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Búsqueda -->
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">Buscar Placa</label>
            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                placeholder="Placa del vehículo..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Tipo de Vehículo -->
        <div>
            <label for="tipo_vehiculo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Vehículo</label>
            <select name="tipo_vehiculo" id="tipo_vehiculo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="todos">Todos</option>
                <option value="carro" {{ request('tipo_vehiculo') == 'carro' ? 'selected' : '' }}>Carro</option>
                <option value="moto" {{ request('tipo_vehiculo') == 'moto' ? 'selected' : '' }}>Moto</option>
            </select>
        </div>

        <!-- Favorecido -->
        <div>
            <label for="fue_favorecido" class="block text-sm font-medium text-gray-700 mb-1">Favorecido</label>
            <select name="fue_favorecido" id="fue_favorecido" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="todos">Todos</option>
                <option value="si" {{ request('fue_favorecido') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ request('fue_favorecido') == 'no' ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.sorteos-parqueadero.participantes', $sorteo->id) }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Participantes -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Residente
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo Vehículo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Placa
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha Inscripción
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Parqueadero Asignado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($participantes as $participante)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $participante->unidad->numero ?? 'N/A' }}
                                @if($participante->unidad && $participante->unidad->torre)
                                    - Torre {{ $participante->unidad->torre }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $participante->residente->user->name ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($participante->tipo_vehiculo == 'carro') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($participante->tipo_vehiculo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $participante->placa }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $participante->fecha_inscripcion->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($participante->parqueadero_asignado)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $participante->parqueadero_asignado }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($participante->fue_favorecido)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Favorecido
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    No favorecido
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                            <p class="text-sm text-gray-500">No hay participantes registrados para este sorteo</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if($participantes->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $participantes->links() }}
        </div>
    @endif
</div>
@endsection
