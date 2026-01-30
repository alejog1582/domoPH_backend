@extends('admin.layouts.app')

@section('title', 'Visitas - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Visitas</h1>
            <p class="mt-2 text-sm text-gray-600">Registro y gestión de visitas</p>
        </div>
        <div>
            <a href="{{ route('admin.visitas.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Visita
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.visitas.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Fecha Desde -->
        <div>
            <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Fecha Hasta -->
        <div>
            <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Estado -->
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" id="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                <option value="finalizada" {{ request('estado') == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                <option value="bloqueada" {{ request('estado') == 'bloqueada' ? 'selected' : '' }}>Bloqueada</option>
            </select>
        </div>

        <!-- Tipo de Visita -->
        <div>
            <label for="tipo_visita" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Visita</label>
            <select name="tipo_visita" id="tipo_visita" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="peatonal" {{ request('tipo_visita') == 'peatonal' ? 'selected' : '' }}>Peatonal</option>
                <option value="vehicular" {{ request('tipo_visita') == 'vehicular' ? 'selected' : '' }}>Vehicular</option>
            </select>
        </div>

        <!-- Unidad -->
        <div>
            <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
            <select name="unidad_id" id="unidad_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todas</option>
                @foreach($unidades as $unidad)
                    <option value="{{ $unidad->id }}" {{ request('unidad_id') == $unidad->id ? 'selected' : '' }}>
                        {{ $unidad->numero }} @if($unidad->torre) - Torre {{ $unidad->torre }} @endif @if($unidad->bloque) - Bloque {{ $unidad->bloque }} @endif
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Búsqueda de Unidad -->
        <div>
            <label for="buscar_unidad" class="block text-sm font-medium text-gray-700 mb-1">Buscar Unidad</label>
            <input type="text" name="buscar_unidad" id="buscar_unidad" value="{{ request('buscar_unidad') }}" 
                placeholder="Número, torre, bloque..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Nombre Visitante -->
        <div>
            <label for="nombre_visitante" class="block text-sm font-medium text-gray-700 mb-1">Nombre Visitante</label>
            <input type="text" name="nombre_visitante" id="nombre_visitante" value="{{ request('nombre_visitante') }}" 
                placeholder="Nombre del visitante..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.visitas.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Visitas -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha Ingreso
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Visitante
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Placa
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Motivo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha Salida
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($visitas as $visita)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $visita->fecha_ingreso->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $visita->unidad->numero ?? 'N/A' }}
                            </div>
                            @if($visita->unidad)
                                <div class="text-xs text-gray-500">
                                    @if($visita->unidad->torre) Torre {{ $visita->unidad->torre }} @endif
                                    @if($visita->unidad->bloque) - Bloque {{ $visita->unidad->bloque }} @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $visita->nombre_visitante }}
                            </div>
                            @if($visita->documento_visitante)
                                <div class="text-xs text-gray-500">
                                    Doc: {{ $visita->documento_visitante }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($visita->tipo_visita) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $visita->placa_vehiculo ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ Str::limit($visita->motivo ?? 'N/A', 40) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($visita->estado == 'activa') bg-green-100 text-green-800
                                @elseif($visita->estado == 'finalizada') bg-blue-100 text-blue-800
                                @elseif($visita->estado == 'cancelada') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($visita->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($visita->fecha_salida)
                                {{ $visita->fecha_salida->format('d/m/Y H:i') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontraron visitas para el mes actual.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $visitas->links() }}
    </div>
</div>
@endsection
