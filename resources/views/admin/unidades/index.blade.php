@extends('admin.layouts.app')

@section('title', 'Unidades - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Unidades</h1>
            <p class="mt-2 text-sm text-gray-600">Administración de unidades habitacionales de la propiedad</p>
        </div>
        <a href="{{ route('admin.unidades.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-plus mr-2"></i>
            Crear Unidad
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Sección de Carga de Archivos -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Sección para cargar la información de las unidades
        </h2>
        
        <div class="mb-4">
            <p class="text-gray-700 mb-2">Para un correcto despliegue de la información debe realizar los siguientes pasos:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-1 mb-4">
                <li><strong>Paso 1:</strong> Descargue la plantilla de ejemplo en excel <a href="{{ route('admin.unidades.template') }}" class="text-red-600 hover:text-red-800 underline font-semibold">aquí</a>.</li>
                <li><strong>Paso 2:</strong> Seleccione el archivo con la información y haga clic en el botón importar:</li>
            </ul>
        </div>

        <form action="{{ route('admin.unidades.import') }}" method="POST" enctype="multipart/form-data" class="mb-4">
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
                <strong>Nota importante:</strong> Cada cargue que realiza reemplaza la información previamente cargada, asegúrese de no borrar la información. <strong>Solo se editarán los datos de las unidades que estén en el archivo cargado.</strong>
            </p>
        </div>
    </div>

    <!-- Sección de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.unidades.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Búsqueda general -->
                <div>
                    <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        id="buscar" 
                        name="buscar" 
                        value="{{ request('buscar') }}" 
                        placeholder="Número, Torre o Bloque"
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
                        <option value="">Todos</option>
                        <option value="apartamento" {{ request('tipo') == 'apartamento' ? 'selected' : '' }}>Apartamento</option>
                        <option value="casa" {{ request('tipo') == 'casa' ? 'selected' : '' }}>Casa</option>
                        <option value="local" {{ request('tipo') == 'local' ? 'selected' : '' }}>Local</option>
                        <option value="parqueadero" {{ request('tipo') == 'parqueadero' ? 'selected' : '' }}>Parqueadero</option>
                        <option value="bodega" {{ request('tipo') == 'bodega' ? 'selected' : '' }}>Bodega</option>
                        <option value="otro" {{ request('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
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
                        <option value="">Todos</option>
                        @if(isset($estadosUnicos) && $estadosUnicos->count() > 0)
                            @foreach($estadosUnicos as $estado)
                                @php
                                    $estadoLabels = [
                                        'ocupada' => 'Ocupada',
                                        'desocupada' => 'Desocupada',
                                        'en_construccion' => 'En Construcción',
                                        'mantenimiento' => 'Mantenimiento'
                                    ];
                                    $label = $estadoLabels[$estado] ?? ucfirst(str_replace('_', ' ', $estado));
                                @endphp
                                <option value="{{ $estado }}" {{ request('estado') == $estado ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        @else
                            <option value="ocupada" {{ request('estado') == 'ocupada' ? 'selected' : '' }}>Ocupada</option>
                            <option value="desocupada" {{ request('estado') == 'desocupada' ? 'selected' : '' }}>Desocupada</option>
                            <option value="en_construccion" {{ request('estado') == 'en_construccion' ? 'selected' : '' }}>En Construcción</option>
                            <option value="mantenimiento" {{ request('estado') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                        @endif
                    </select>
                </div>

                <!-- Filtro Área -->
                <div>
                    <label for="area_m2" class="block text-sm font-medium text-gray-700 mb-2">Área (m²)</label>
                    <select 
                        id="area_m2" 
                        name="area_m2" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todas</option>
                        @foreach($areasUnicas as $area)
                            <option value="{{ $area }}" {{ request('area_m2') == $area ? 'selected' : '' }}>
                                {{ number_format((float)$area, 2) }} m²
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Coeficiente -->
                <div>
                    <label for="coeficiente" class="block text-sm font-medium text-gray-700 mb-2">Coeficiente</label>
                    <select 
                        id="coeficiente" 
                        name="coeficiente" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        @foreach($coeficientesUnicos as $coef)
                            <option value="{{ $coef }}" {{ request('coeficiente') == $coef ? 'selected' : '' }}>
                                {{ $coef }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Habitaciones -->
                <div>
                    <label for="habitaciones" class="block text-sm font-medium text-gray-700 mb-2">Habitaciones</label>
                    <select 
                        id="habitaciones" 
                        name="habitaciones" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todas</option>
                        <option value="1" {{ request('habitaciones') == '1' ? 'selected' : '' }}>1</option>
                        <option value="2" {{ request('habitaciones') == '2' ? 'selected' : '' }}>2</option>
                        <option value="3" {{ request('habitaciones') == '3' ? 'selected' : '' }}>3</option>
                        <option value="4" {{ request('habitaciones') == '4' ? 'selected' : '' }}>4</option>
                        <option value="5" {{ request('habitaciones') == '5' ? 'selected' : '' }}>5+</option>
                    </select>
                </div>

                <!-- Filtro Baños -->
                <div>
                    <label for="banos" class="block text-sm font-medium text-gray-700 mb-2">Baños</label>
                    <select 
                        id="banos" 
                        name="banos" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="1" {{ request('banos') == '1' ? 'selected' : '' }}>1</option>
                        <option value="2" {{ request('banos') == '2' ? 'selected' : '' }}>2</option>
                        <option value="3" {{ request('banos') == '3' ? 'selected' : '' }}>3</option>
                        <option value="4" {{ request('banos') == '4' ? 'selected' : '' }}>4+</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.unidades.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    <i class="fas fa-times mr-1"></i> Limpiar filtros
                </a>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <i class="fas fa-search mr-2"></i>
                        Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Unidades -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NÚMERO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">TORRE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">BLOQUE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">TIPO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ÁREA (m²)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">COEFICIENTE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">HABITACIONES</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">BAÑOS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">OBSERVACIONES</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($unidades as $unidad)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $unidad->numero ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $unidad->torre ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $unidad->bloque ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($unidad->tipo ?? '-') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $unidad->area_m2 ? number_format($unidad->area_m2, 2) : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $unidad->coeficiente ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $unidad->habitaciones ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $unidad->banos ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $estadoColors = [
                                        'ocupada' => 'bg-green-100 text-green-800',
                                        'desocupada' => 'bg-gray-100 text-gray-800',
                                        'en_construccion' => 'bg-yellow-100 text-yellow-800',
                                        'mantenimiento' => 'bg-red-100 text-red-800',
                                    ];
                                    $color = $estadoColors[$unidad->estado] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $unidad->estado ?? '-')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="truncate max-w-xs block" title="{{ $unidad->observaciones }}">
                                    {{ $unidad->observaciones ? \Illuminate\Support\Str::limit($unidad->observaciones, 50) : '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.unidades.edit', $unidad->id) }}" class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        onclick="confirmarEliminacion({{ $unidad->id }}, '{{ $unidad->numero }}')" 
                                        class="text-red-600 hover:text-red-900" 
                                        title="Eliminar"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron unidades. 
                                @if(request('buscar'))
                                    Intenta con otro criterio de búsqueda.
                                @else
                                    Carga un archivo para comenzar.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($unidades->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $unidades->links() }}
            </div>
        @endif
    </div>

@else
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>No hay propiedad asignada. Contacta al superadministrador.</span>
        </div>
    </div>
@endif

    <!-- Modal de Confirmación de Eliminación -->
    <div id="modalEliminar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-5">¿Está seguro?</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        ¿Está seguro de que desea eliminar la unidad <strong id="unidadNumero"></strong>? Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="flex items-center justify-center space-x-4 px-4 py-3 mt-4">
                    <button 
                        type="button" 
                        onclick="cerrarModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
                    >
                        Cancelar
                    </button>
                    <form id="formEliminar" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    function updateFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : 'Sin archivos seleccionados';
        document.getElementById('fileName').textContent = fileName;
    }

    function confirmarEliminacion(id, numero) {
        document.getElementById('unidadNumero').textContent = numero;
        document.getElementById('formEliminar').action = '{{ route("admin.unidades.destroy", ":id") }}'.replace(':id', id);
        document.getElementById('modalEliminar').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalEliminar').classList.add('hidden');
    }

    // Cerrar modal al hacer click fuera de él
    window.onclick = function(event) {
        const modal = document.getElementById('modalEliminar');
        if (event.target == modal) {
            cerrarModal();
        }
    }
</script>
@endpush
@endsection
