@extends('admin.layouts.app')

@section('title', 'Sorteos Parqueaderos - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sorteos Parqueaderos</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de sorteos de parqueaderos</p>
        </div>
        <div>
            <a href="{{ route('admin.sorteos-parqueadero.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Nuevo Sorteo
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.sorteos-parqueadero.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Búsqueda -->
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                placeholder="Título del sorteo..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Estado -->
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" id="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="todos">Todos</option>
                <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
            </select>
        </div>

        <!-- Activo -->
        <div>
            <label for="activo" class="block text-sm font-medium text-gray-700 mb-1">Activo</label>
            <select name="activo" id="activo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="todos">Todos</option>
                <option value="si" {{ request('activo') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ request('activo') == 'no' ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Fecha Sorteo Desde -->
        <div>
            <label for="fecha_sorteo_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Sorteo Desde</label>
            <input type="date" name="fecha_sorteo_desde" id="fecha_sorteo_desde" value="{{ request('fecha_sorteo_desde') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Fecha Sorteo Hasta -->
        <div>
            <label for="fecha_sorteo_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Sorteo Hasta</label>
            <input type="date" name="fecha_sorteo_hasta" id="fecha_sorteo_hasta" value="{{ request('fecha_sorteo_hasta') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.sorteos-parqueadero.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Cards de Sorteos -->
@if($sorteos->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($sorteos as $sorteo)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                {{ $sorteo->titulo }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($sorteo->estado == 'activo') bg-green-100 text-green-800
                                    @elseif($sorteo->estado == 'cerrado') bg-gray-100 text-gray-800
                                    @elseif($sorteo->estado == 'anulado') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($sorteo->estado) }}
                                </span>
                                @if($sorteo->activo)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Inactivo
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($sorteo->descripcion)
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                            {{ Str::limit($sorteo->descripcion, 100) }}
                        </p>
                    @endif

                    <!-- Fechas -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                            <span class="font-medium">Inicio recolección:</span>
                            <span class="ml-2">{{ $sorteo->fecha_inicio_recoleccion->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-times mr-2 text-gray-400"></i>
                            <span class="font-medium">Fin recolección:</span>
                            <span class="ml-2">{{ $sorteo->fecha_fin_recoleccion->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-check mr-2 text-gray-400"></i>
                            <span class="font-medium">Fecha sorteo:</span>
                            <span class="ml-2 font-semibold text-blue-600">{{ $sorteo->fecha_sorteo->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- Participantes -->
                    <div class="mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-users mr-2 text-gray-400"></i>
                            <span class="font-medium">Participantes:</span>
                            <span class="ml-2">{{ $sorteo->participantes->count() }}</span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div class="text-xs text-gray-500">
                            Creado: {{ $sorteo->created_at->format('d/m/Y') }}
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.sorteos-parqueadero.participantes', $sorteo->id) }}" 
                                class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors">
                                <i class="fas fa-users mr-1"></i>Ver Participantes
                            </a>
                            <a href="{{ route('admin.sorteos-parqueadero.edit', $sorteo->id) }}" 
                                class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $sorteos->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-car text-gray-400 text-6xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay sorteos registrados</h3>
        <p class="text-sm text-gray-500 mb-6">Comienza creando tu primer sorteo de parqueaderos</p>
        <a href="{{ route('admin.sorteos-parqueadero.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Sorteo
        </a>
    </div>
@endif
@endsection
