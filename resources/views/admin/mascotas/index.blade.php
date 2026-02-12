@extends('admin.layouts.app')

@section('title', 'Mascotas - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Mascotas</h1>
            <p class="mt-2 text-sm text-gray-600">Administración de mascotas de la propiedad</p>
        </div>
        <a href="{{ route('admin.mascotas.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-plus mr-2"></i>
            Crear Mascota
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Sección de Carga de Archivos -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Sección para cargar la información de las mascotas
        </h2>
        
        <div class="mb-4">
            <p class="text-gray-700 mb-2">Para un correcto despliegue de la información debe realizar los siguientes pasos:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-1 mb-4">
                <li><strong>Paso 1:</strong> Descargue la plantilla de ejemplo en excel <a href="{{ route('admin.mascotas.template') }}" class="text-red-600 hover:text-red-800 underline font-semibold">aquí</a>.</li>
                <li><strong>Paso 2:</strong> Seleccione el archivo con la información y haga clic en el botón importar:</li>
            </ul>
        </div>

        <form action="{{ route('admin.mascotas.import') }}" method="POST" enctype="multipart/form-data" class="mb-4">
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
                <strong>Nota importante:</strong> Cada cargue que realiza reemplaza la información previamente cargada, asegúrese de no borrar la información. <strong>Solo se editarán los datos de las mascotas que estén en el archivo cargado.</strong>
            </p>
        </div>
    </div>

    <!-- Sección de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.mascotas.index') }}">
            @if(request('residente_id'))
                <input type="hidden" name="residente_id" value="{{ request('residente_id') }}">
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Búsqueda general -->
                <div>
                    <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        id="buscar" 
                        name="buscar" 
                        value="{{ request('buscar') }}" 
                        placeholder="Nombre, Raza o Chip"
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
                        @foreach($tiposUnicos as $tipo)
                            @php
                                $tipoLabels = [
                                    'perro' => 'Perro',
                                    'gato' => 'Gato',
                                    'ave' => 'Ave',
                                    'reptil' => 'Reptil',
                                    'roedor' => 'Roedor',
                                    'otro' => 'Otro'
                                ];
                                $label = $tipoLabels[$tipo] ?? ucfirst($tipo);
                            @endphp
                            <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
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
                            @php
                                $unidadLabel = $unidad->numero;
                                if ($unidad->torre) $unidadLabel .= ' - ' . $unidad->torre;
                                if ($unidad->bloque) $unidadLabel .= ' - ' . $unidad->bloque;
                            @endphp
                            <option value="{{ $unidad->id }}" {{ request('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                {{ $unidadLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Vacunado -->
                <div>
                    <label for="vacunado" class="block text-sm font-medium text-gray-700 mb-2">Vacunado</label>
                    <select 
                        id="vacunado" 
                        name="vacunado" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="1" {{ request('vacunado') == '1' ? 'selected' : '' }}>Sí</option>
                        <option value="0" {{ request('vacunado') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.mascotas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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

    <!-- Tabla de Mascotas -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="table-container">
            <table class="table-domoph min-w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NOMBRE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">TIPO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">RAZA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">SEXO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">TAMAÑO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">UNIDAD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">RESIDENTE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">VACUNADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTERILIZADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO SALUD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mascotas as $mascota)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mascota->nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $tipoLabels = [
                                        'perro' => 'Perro',
                                        'gato' => 'Gato',
                                        'ave' => 'Ave',
                                        'reptil' => 'Reptil',
                                        'roedor' => 'Roedor',
                                        'otro' => 'Otro'
                                    ];
                                    $tipoLabel = $tipoLabels[$mascota->tipo] ?? ucfirst($mascota->tipo);
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $tipoLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mascota->raza ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $sexoLabels = [
                                        'macho' => 'Macho',
                                        'hembra' => 'Hembra',
                                        'desconocido' => 'Desconocido'
                                    ];
                                    $sexoLabel = $sexoLabels[$mascota->sexo] ?? ucfirst($mascota->sexo);
                                @endphp
                                {{ $sexoLabel }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($mascota->tamanio)
                                    @php
                                        $tamanioLabels = [
                                            'pequeño' => 'Pequeño',
                                            'mediano' => 'Mediano',
                                            'grande' => 'Grande'
                                        ];
                                        $tamanioLabel = $tamanioLabels[$mascota->tamanio] ?? ucfirst($mascota->tamanio);
                                    @endphp
                                    {{ $tamanioLabel }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $unidadLabel = $mascota->unidad->numero ?? '-';
                                    if ($mascota->unidad && $mascota->unidad->torre) $unidadLabel .= ' - ' . $mascota->unidad->torre;
                                    if ($mascota->unidad && $mascota->unidad->bloque) $unidadLabel .= ' - ' . $mascota->unidad->bloque;
                                @endphp
                                {{ $unidadLabel }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $mascota->residente->user->nombre ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($mascota->vacunado)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Sí
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($mascota->esterilizado)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Sí
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($mascota->estado_salud)
                                    @php
                                        $estadoLabels = [
                                            'saludable' => 'Saludable',
                                            'en_tratamiento' => 'En Tratamiento',
                                            'crónico' => 'Crónico',
                                            'desconocido' => 'Desconocido'
                                        ];
                                        $estadoLabel = $estadoLabels[$mascota->estado_salud] ?? ucfirst(str_replace('_', ' ', $mascota->estado_salud));
                                        $estadoColors = [
                                            'saludable' => 'bg-green-100 text-green-800',
                                            'en_tratamiento' => 'bg-yellow-100 text-yellow-800',
                                            'crónico' => 'bg-orange-100 text-orange-800',
                                            'desconocido' => 'bg-gray-100 text-gray-800',
                                        ];
                                        $color = $estadoColors[$mascota->estado_salud] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                        {{ $estadoLabel }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.mascotas.edit', $mascota->id) }}" class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        onclick="confirmarEliminacion({{ $mascota->id }}, '{{ $mascota->nombre }}')" 
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
                                No se encontraron mascotas. 
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
        @if($mascotas->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $mascotas->links() }}
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
                        ¿Está seguro de que desea eliminar la mascota <strong id="mascotaNombre"></strong>? Esta acción no se puede deshacer.
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

    function confirmarEliminacion(id, nombre) {
        document.getElementById('mascotaNombre').textContent = nombre;
        document.getElementById('formEliminar').action = '{{ route("admin.mascotas.destroy", ":id") }}'.replace(':id', id);
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
