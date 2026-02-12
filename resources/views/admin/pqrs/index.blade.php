@extends('admin.layouts.app')

@section('title', 'PQRS - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">PQRS</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de Peticiones, Quejas, Reclamos y Sugerencias</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.pqrs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Estado -->
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" id="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Radicada y En Proceso (Por defecto)</option>
                <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                <option value="radicada" {{ request('estado') == 'radicada' ? 'selected' : '' }}>Radicada</option>
                <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                <option value="respondida" {{ request('estado') == 'respondida' ? 'selected' : '' }}>Respondida</option>
                <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                <option value="rechazada" {{ request('estado') == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
            </select>
        </div>

        <!-- Tipo -->
        <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select name="tipo" id="tipo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="peticion" {{ request('tipo') == 'peticion' ? 'selected' : '' }}>Petición</option>
                <option value="queja" {{ request('tipo') == 'queja' ? 'selected' : '' }}>Queja</option>
                <option value="reclamo" {{ request('tipo') == 'reclamo' ? 'selected' : '' }}>Reclamo</option>
                <option value="sugerencia" {{ request('tipo') == 'sugerencia' ? 'selected' : '' }}>Sugerencia</option>
            </select>
        </div>

        <!-- Categoría -->
        <div>
            <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
            <select name="categoria" id="categoria" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todas</option>
                <option value="administracion" {{ request('categoria') == 'administracion' ? 'selected' : '' }}>Administración</option>
                <option value="mantenimiento" {{ request('categoria') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                <option value="seguridad" {{ request('categoria') == 'seguridad' ? 'selected' : '' }}>Seguridad</option>
                <option value="convivencia" {{ request('categoria') == 'convivencia' ? 'selected' : '' }}>Convivencia</option>
                <option value="servicios" {{ request('categoria') == 'servicios' ? 'selected' : '' }}>Servicios</option>
                <option value="otro" {{ request('categoria') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
        </div>

        <!-- Prioridad -->
        <div>
            <label for="prioridad" class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
            <select name="prioridad" id="prioridad" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todas</option>
                <option value="baja" {{ request('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                <option value="media" {{ request('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                <option value="alta" {{ request('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="critica" {{ request('prioridad') == 'critica' ? 'selected' : '' }}>Crítica</option>
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

        <!-- Búsqueda -->
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                placeholder="Asunto, radicado, descripción..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Fecha Desde -->
        <div>
            <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Radicación Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Fecha Hasta -->
        <div>
            <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Radicación Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.pqrs.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de PQRS -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="table-container">
        <table class="table-domoph min-w-full">
            <thead>
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Radicado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Asunto
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Prioridad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($pqrs as $pqr)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $pqr->numero_radicado }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $pqr->fecha_radicacion->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($pqr->unidad)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $pqr->unidad->numero }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($pqr->unidad->torre) Torre {{ $pqr->unidad->torre }} @endif
                                    @if($pqr->unidad->bloque) - Bloque {{ $pqr->unidad->bloque }} @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">General</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($pqr->tipo) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ Str::limit($pqr->asunto, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($pqr->prioridad == 'critica') bg-red-100 text-red-800
                                @elseif($pqr->prioridad == 'alta') bg-orange-100 text-orange-800
                                @elseif($pqr->prioridad == 'media') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($pqr->prioridad) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($pqr->estado == 'radicada') bg-blue-100 text-blue-800
                                @elseif($pqr->estado == 'en_proceso') bg-yellow-100 text-yellow-800
                                @elseif($pqr->estado == 'respondida') bg-green-100 text-green-800
                                @elseif($pqr->estado == 'cerrada') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $pqr->estado)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.pqrs.edit', $pqr) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit mr-1"></i>Gestionar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontraron PQRS.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $pqrs->links() }}
    </div>
</div>
@endsection
