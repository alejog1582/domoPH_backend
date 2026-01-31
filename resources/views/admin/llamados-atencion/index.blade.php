@extends('admin.layouts.app')

@section('title', 'Llamados de Atención - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Llamados de Atención</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de llamados de atención por incumplimiento</p>
        </div>
        <div>
            <a href="{{ route('admin.llamados-atencion.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Llamado de Atención
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.llamados-atencion.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Estado -->
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" id="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Abierto y En Proceso (Por defecto)</option>
                <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                <option value="abierto" {{ request('estado') == 'abierto' ? 'selected' : '' }}>Abierto</option>
                <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
            </select>
        </div>

        <!-- Tipo -->
        <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select name="tipo" id="tipo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="convivencia" {{ request('tipo') == 'convivencia' ? 'selected' : '' }}>Convivencia</option>
                <option value="ruido" {{ request('tipo') == 'ruido' ? 'selected' : '' }}>Ruido</option>
                <option value="mascotas" {{ request('tipo') == 'mascotas' ? 'selected' : '' }}>Mascotas</option>
                <option value="parqueadero" {{ request('tipo') == 'parqueadero' ? 'selected' : '' }}>Parqueadero</option>
                <option value="seguridad" {{ request('tipo') == 'seguridad' ? 'selected' : '' }}>Seguridad</option>
                <option value="otro" {{ request('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
        </div>

        <!-- Nivel -->
        <div>
            <label for="nivel" class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
            <select name="nivel" id="nivel" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="leve" {{ request('nivel') == 'leve' ? 'selected' : '' }}>Leve</option>
                <option value="moderado" {{ request('nivel') == 'moderado' ? 'selected' : '' }}>Moderado</option>
                <option value="grave" {{ request('nivel') == 'grave' ? 'selected' : '' }}>Grave</option>
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
                placeholder="Motivo, descripción..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Fecha Desde -->
        <div>
            <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Evento Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Fecha Hasta -->
        <div>
            <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Evento Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Reincidencia -->
        <div>
            <label for="es_reincidencia" class="block text-sm font-medium text-gray-700 mb-1">Reincidencia</label>
            <select name="es_reincidencia" id="es_reincidencia" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="1" {{ request('es_reincidencia') == '1' ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ request('es_reincidencia') == '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.llamados-atencion.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Llamados de Atención -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha Evento
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Motivo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nivel
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Reincidencia
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($llamados as $llamado)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $llamado->fecha_evento->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($llamado->unidad)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $llamado->unidad->numero }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($llamado->unidad->torre) Torre {{ $llamado->unidad->torre }} @endif
                                    @if($llamado->unidad->bloque) - Bloque {{ $llamado->unidad->bloque }} @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">General</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($llamado->tipo) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ Str::limit($llamado->motivo, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($llamado->nivel == 'grave') bg-red-100 text-red-800
                                @elseif($llamado->nivel == 'moderado') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($llamado->nivel) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($llamado->estado == 'abierto') bg-blue-100 text-blue-800
                                @elseif($llamado->estado == 'en_proceso') bg-yellow-100 text-yellow-800
                                @elseif($llamado->estado == 'cerrado') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $llamado->estado)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($llamado->es_reincidencia)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Sí
                                </span>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.llamados-atencion.edit', $llamado->id) }}" 
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-cog mr-1"></i>
                                Gestionar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontraron llamados de atención.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $llamados->links() }}
    </div>
</div>
@endsection
