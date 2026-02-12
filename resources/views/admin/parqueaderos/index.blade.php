@extends('admin.layouts.app')

@section('title', 'Parqueaderos - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Parqueaderos</h1>
            <p class="mt-2 text-sm text-gray-600">Administración de parqueaderos de la propiedad</p>
        </div>
        <a href="{{ route('admin.parqueaderos.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-plus mr-2"></i>
            Crear Parqueadero
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Sección de Carga de Archivos -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Sección para cargar la información de los parqueaderos
        </h2>
        
        <div class="mb-4">
            <p class="text-gray-700 mb-2">Para un correcto despliegue de la información debe realizar los siguientes pasos:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-1 mb-4">
                <li><strong>Paso 1:</strong> Descargue la plantilla de ejemplo en excel <a href="{{ route('admin.parqueaderos.template') }}" class="text-red-600 hover:text-red-800 underline font-semibold">aquí</a>.</li>
                <li><strong>Paso 2:</strong> Seleccione el archivo con la información y haga clic en el botón importar:</li>
            </ul>
        </div>

        <form action="{{ route('admin.parqueaderos.import') }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="flex items-center space-x-4">
                <label for="archivo" class="cursor-pointer">
                    <span class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300">
                        <i class="fas fa-file-upload mr-2"></i>
                        Seleccionar archivo
                    </span>
                    <input type="file" id="archivo" name="archivo" accept=".xlsx,.xls,.csv" class="hidden" onchange="updateFileName(this)">
                </label>
                <span id="fileName" class="text-gray-600 text-sm">Sin archivos seleccionados</span>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    <i class="fas fa-upload mr-2"></i>
                    Importar
                </button>
            </div>
        </form>

        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <p class="text-sm">
                <strong>Nota importante:</strong> Cada cargue que realiza reemplaza la información previamente cargada, asegúrese de no borrar la información. <strong>Solo se editarán los datos de los parqueaderos que estén en el archivo cargado.</strong>
            </p>
        </div>
    </div>

    <!-- Sección de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.parqueaderos.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Búsqueda general -->
                <div>
                    <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        id="buscar" 
                        name="buscar" 
                        value="{{ request('buscar') }}" 
                        placeholder="Código o nivel"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Filtro Tipo -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select 
                        id="tipo" 
                        name="tipo" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="todos">Todos</option>
                        <option value="privado" {{ request('tipo') == 'privado' ? 'selected' : '' }}>Privado</option>
                        <option value="comunal" {{ request('tipo') == 'comunal' ? 'selected' : '' }}>Comunal</option>
                        <option value="visitantes" {{ request('tipo') == 'visitantes' ? 'selected' : '' }}>Visitantes</option>
                    </select>
                </div>

                <!-- Filtro Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select 
                        id="estado" 
                        name="estado" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="todos">Todos</option>
                        <option value="disponible" {{ request('estado') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="asignado" {{ request('estado') == 'asignado' ? 'selected' : '' }}>Asignado</option>
                        <option value="en_mantenimiento" {{ request('estado') == 'en_mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
                        <option value="inhabilitado" {{ request('estado') == 'inhabilitado' ? 'selected' : '' }}>Inhabilitado</option>
                    </select>
                </div>

                <!-- Filtro Unidad -->
                <div>
                    <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-2">Unidad</label>
                    <select 
                        id="unidad_id" 
                        name="unidad_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todas</option>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->id }}" {{ request('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                {{ $unidad->numero }} @if($unidad->torre) - Torre {{ $unidad->torre }} @endif @if($unidad->bloque) - Bloque {{ $unidad->bloque }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Es Cubierto -->
                <div>
                    <label for="es_cubierto" class="block text-sm font-medium text-gray-700 mb-2">Es Cubierto</label>
                    <select 
                        id="es_cubierto" 
                        name="es_cubierto" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="todos">Todos</option>
                        <option value="si" {{ request('es_cubierto') == 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ request('es_cubierto') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
                <a href="{{ route('admin.parqueaderos.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Parqueaderos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-domoph min-w-full">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Código
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo Vehículo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nivel
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cubierto
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Unidad Asignada
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Residente Responsable
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parqueaderos as $parqueadero)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $parqueadero->codigo }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($parqueadero->tipo == 'privado') bg-blue-100 text-blue-800
                                    @elseif($parqueadero->tipo == 'comunal') bg-green-100 text-green-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($parqueadero->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($parqueadero->tipo_vehiculo)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($parqueadero->tipo_vehiculo == 'carro') bg-purple-100 text-purple-800
                                        @else bg-orange-100 text-orange-800
                                        @endif">
                                        <i class="fas {{ $parqueadero->tipo_vehiculo == 'carro' ? 'fa-car' : 'fa-motorcycle' }} mr-1"></i>
                                        {{ ucfirst($parqueadero->tipo_vehiculo) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $parqueadero->nivel ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($parqueadero->estado == 'disponible') bg-green-100 text-green-800
                                    @elseif($parqueadero->estado == 'asignado') bg-blue-100 text-blue-800
                                    @elseif($parqueadero->estado == 'en_mantenimiento') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $parqueadero->estado)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($parqueadero->es_cubierto)
                                    <span class="text-green-600"><i class="fas fa-check"></i> Sí</span>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-times"></i> No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($parqueadero->unidad)
                                    {{ $parqueadero->unidad->numero }}
                                    @if($parqueadero->unidad->torre) Torre {{ $parqueadero->unidad->torre }} @endif
                                    @if($parqueadero->unidad->bloque) Bloque {{ $parqueadero->unidad->bloque }} @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($parqueadero->residenteResponsable)
                                    {{ $parqueadero->residenteResponsable->user->nombre ?? 'N/A' }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.parqueaderos.edit', $parqueadero->id) }}" 
                                        class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.parqueaderos.destroy', $parqueadero->id) }}" 
                                        method="POST" 
                                        onsubmit="return confirm('¿Está seguro de eliminar este parqueadero?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <i class="fas fa-car text-gray-400 text-4xl mb-4"></i>
                                <p class="text-sm text-gray-500">No hay parqueaderos registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($parqueaderos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $parqueaderos->links() }}
            </div>
        @endif
    </div>
@else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-exclamation-triangle text-yellow-400 text-6xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay propiedad asignada</h3>
        <p class="text-sm text-gray-500">Por favor, seleccione una propiedad para continuar.</p>
    </div>
@endif

<script>
function updateFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : 'Sin archivos seleccionados';
    document.getElementById('fileName').textContent = fileName;
}
</script>
@endsection
