@extends('admin.layouts.app')

@section('title', 'Correspondencia - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Correspondencia</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de correspondencia recibida</p>
        </div>
        <div>
            <a href="{{ route('admin.correspondencias.cargar') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-upload mr-2"></i>
                Cargar Correspondencia
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.correspondencias.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <option value="recibido" {{ request('estado') == 'recibido' ? 'selected' : '' }}>Recibido</option>
                <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                <option value="devuelto" {{ request('estado') == 'devuelto' ? 'selected' : '' }}>Devuelto</option>
                <option value="perdido" {{ request('estado') == 'perdido' ? 'selected' : '' }}>Perdido</option>
            </select>
        </div>

        <!-- Tipo -->
        <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select name="tipo" id="tipo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="paquete" {{ request('tipo') == 'paquete' ? 'selected' : '' }}>Paquete</option>
                <option value="documento" {{ request('tipo') == 'documento' ? 'selected' : '' }}>Documento</option>
                <option value="factura" {{ request('tipo') == 'factura' ? 'selected' : '' }}>Factura</option>
                <option value="domicilio" {{ request('tipo') == 'domicilio' ? 'selected' : '' }}>Domicilio</option>
                <option value="otro" {{ request('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
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

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.correspondencias.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Correspondencias -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha Recepción
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Remitente
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Descripción
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha Entrega
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($correspondencias as $correspondencia)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $correspondencia->fecha_recepcion->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $correspondencia->unidad->numero ?? 'N/A' }}
                            </div>
                            @if($correspondencia->unidad)
                                <div class="text-xs text-gray-500">
                                    @if($correspondencia->unidad->torre) Torre {{ $correspondencia->unidad->torre }} @endif
                                    @if($correspondencia->unidad->bloque) - Bloque {{ $correspondencia->unidad->bloque }} @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($correspondencia->tipo) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $correspondencia->remitente ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ Str::limit($correspondencia->descripcion ?? 'N/A', 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($correspondencia->estado == 'entregado') bg-green-100 text-green-800
                                @elseif($correspondencia->estado == 'devuelto') bg-yellow-100 text-yellow-800
                                @elseif($correspondencia->estado == 'perdido') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ ucfirst($correspondencia->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($correspondencia->fecha_entrega)
                                {{ $correspondencia->fecha_entrega->format('d/m/Y H:i') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontró correspondencia para el mes actual.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $correspondencias->links() }}
    </div>
</div>
@endsection
