@extends('admin.layouts.app')

@section('title', 'Zonas Comunes - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Zonas Comunes</h1>
            <p class="mt-2 text-sm text-gray-600">Administración de zonas comunes de la propiedad</p>
        </div>
        <a href="{{ route('admin.zonas-sociales.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-plus mr-2"></i>
            Crear Zona Común
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Sección de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.zonas-sociales.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <!-- Búsqueda general -->
                <div>
                    <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        id="buscar" 
                        name="buscar" 
                        value="{{ request('buscar') }}" 
                        placeholder="Nombre, Descripción o Ubicación"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
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
                        <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                        <option value="inactiva" {{ request('estado') == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                        <option value="mantenimiento" {{ request('estado') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    </select>
                </div>

                <!-- Filtro Activo -->
                <div>
                    <label for="activo" class="block text-sm font-medium text-gray-700 mb-2">Activo</label>
                    <select 
                        id="activo" 
                        name="activo" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Sí</option>
                        <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.zonas-sociales.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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

    <!-- Tabla de Zonas Comunes -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NOMBRE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">UBICACIÓN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CAPACIDAD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">HORARIOS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">IMÁGENES</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($zonasSociales as $zona)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $zona->nombre }}</div>
                                @if($zona->descripcion)
                                    <div class="text-sm text-gray-500">{{ Str::limit($zona->descripcion, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $zona->ubicacion ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $zona->capacidad_maxima }} personas
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $estadoLabels = [
                                        'activa' => ['label' => 'Activa', 'class' => 'bg-green-100 text-green-800'],
                                        'inactiva' => ['label' => 'Inactiva', 'class' => 'bg-gray-100 text-gray-800'],
                                        'mantenimiento' => ['label' => 'Mantenimiento', 'class' => 'bg-yellow-100 text-yellow-800'],
                                    ];
                                    $estadoInfo = $estadoLabels[$zona->estado] ?? ['label' => ucfirst($zona->estado), 'class' => 'bg-gray-100 text-gray-800'];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $estadoInfo['class'] }}">
                                    {{ $estadoInfo['label'] }}
                                </span>
                                @if(!$zona->activo)
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $zona->horarios->count() }} horario(s)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $zona->imagenes->count() }} imagen(es)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.zonas-sociales.edit', $zona->id) }}" class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        onclick="confirmarEliminacion({{ $zona->id }}, '{{ $zona->nombre }}')" 
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
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron zonas comunes. 
                                @if(request('buscar'))
                                    Intenta con otro criterio de búsqueda.
                                @else
                                    <a href="{{ route('admin.zonas-sociales.create') }}" class="text-blue-600 hover:text-blue-800 underline">Crea una zona común</a> para comenzar.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($zonasSociales->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $zonasSociales->links() }}
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
                    ¿Está seguro de que desea eliminar la zona común <strong id="zonaNombre"></strong>? Esta acción no se puede deshacer.
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
    function confirmarEliminacion(id, nombre) {
        document.getElementById('zonaNombre').textContent = nombre;
        document.getElementById('formEliminar').action = '{{ route("admin.zonas-sociales.destroy", ":id") }}'.replace(':id', id);
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
