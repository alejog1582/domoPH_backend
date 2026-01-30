@extends('admin.layouts.app')

@section('title', 'Autorizaciones - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Autorizaciones</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de autorizaciones de acceso</p>
        </div>
        <div>
            <a href="{{ route('admin.autorizaciones.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Autorización
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.autorizaciones.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Estado -->
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" id="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                <option value="vencida" {{ request('estado') == 'vencida' ? 'selected' : '' }}>Vencida</option>
                <option value="suspendida" {{ request('estado') == 'suspendida' ? 'selected' : '' }}>Suspendida</option>
            </select>
        </div>

        <!-- Tipo de Autorizado -->
        <div>
            <label for="tipo_autorizado" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Autorizado</label>
            <select name="tipo_autorizado" id="tipo_autorizado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="familiar" {{ request('tipo_autorizado') == 'familiar' ? 'selected' : '' }}>Familiar</option>
                <option value="empleado" {{ request('tipo_autorizado') == 'empleado' ? 'selected' : '' }}>Empleado</option>
                <option value="aseo" {{ request('tipo_autorizado') == 'aseo' ? 'selected' : '' }}>Aseo</option>
                <option value="mantenimiento" {{ request('tipo_autorizado') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                <option value="proveedor" {{ request('tipo_autorizado') == 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                <option value="otro" {{ request('tipo_autorizado') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
        </div>

        <!-- Tipo de Acceso -->
        <div>
            <label for="tipo_acceso" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Acceso</label>
            <select name="tipo_acceso" id="tipo_acceso" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="peatonal" {{ request('tipo_acceso') == 'peatonal' ? 'selected' : '' }}>Peatonal</option>
                <option value="vehicular" {{ request('tipo_acceso') == 'vehicular' ? 'selected' : '' }}>Vehicular</option>
                <option value="ambos" {{ request('tipo_acceso') == 'ambos' ? 'selected' : '' }}>Ambos</option>
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

        <!-- Nombre Autorizado -->
        <div>
            <label for="nombre_autorizado" class="block text-sm font-medium text-gray-700 mb-1">Nombre Autorizado</label>
            <input type="text" name="nombre_autorizado" id="nombre_autorizado" value="{{ request('nombre_autorizado') }}" 
                placeholder="Nombre del autorizado..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.autorizaciones.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Autorizaciones -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nombre Autorizado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acceso
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Placa
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Período
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($autorizaciones as $autorizacion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $autorizacion->nombre_autorizado }}
                            </div>
                            @if($autorizacion->documento_autorizado)
                                <div class="text-xs text-gray-500">
                                    Doc: {{ $autorizacion->documento_autorizado }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($autorizacion->unidad)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $autorizacion->unidad->numero }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($autorizacion->unidad->torre) Torre {{ $autorizacion->unidad->torre }} @endif
                                    @if($autorizacion->unidad->bloque) - Bloque {{ $autorizacion->unidad->bloque }} @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">General</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($autorizacion->tipo_autorizado) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($autorizacion->tipo_acceso) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $autorizacion->placa_vehiculo ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($autorizacion->fecha_inicio || $autorizacion->fecha_fin)
                                <div>
                                    @if($autorizacion->fecha_inicio)
                                        Desde: {{ $autorizacion->fecha_inicio->format('d/m/Y') }}
                                    @endif
                                </div>
                                <div>
                                    @if($autorizacion->fecha_fin)
                                        Hasta: {{ $autorizacion->fecha_fin->format('d/m/Y') }}
                                    @else
                                        <span class="text-gray-400">Sin límite</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">Indefinido</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($autorizacion->estado == 'activa') bg-green-100 text-green-800
                                @elseif($autorizacion->estado == 'vencida') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($autorizacion->estado) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontraron autorizaciones.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $autorizaciones->links() }}
    </div>
</div>
@endsection
