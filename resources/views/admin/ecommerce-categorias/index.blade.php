@extends('admin.layouts.app')

@section('title', 'Categorías Ecommerce - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Categorías Ecommerce</h1>
            <p class="mt-2 text-sm text-gray-600">Administración de categorías para las publicaciones del ecommerce</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('ecommerce-categorias.create'))
        <a href="{{ route('admin.ecommerce-categorias.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-plus mr-2"></i>
            Crear Categoría
        </a>
        @endif
    </div>
</div>

@if($propiedad)
    <!-- Sección de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.ecommerce-categorias.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Búsqueda general -->
                <div>
                    <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        id="buscar" 
                        name="buscar" 
                        value="{{ request('buscar') }}" 
                        placeholder="Nombre o descripción"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Filtro Activo -->
                <div>
                    <label for="activo" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select 
                        id="activo" 
                        name="activo" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activas</option>
                        <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivas</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.ecommerce-categorias.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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

    <!-- Tabla de Categorías -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NOMBRE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">SLUG</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">DESCRIPCIÓN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ICONO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categorias as $categoria)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $categoria->nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $categoria->slug }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ Str::limit($categoria->descripcion ?? '-', 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($categoria->icono)
                                    <i class="fas fa-{{ $categoria->icono }}"></i> {{ $categoria->icono }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($categoria->activo)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Activa
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactiva
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    @if(\App\Helpers\AdminHelper::hasPermission('ecommerce-categorias.view'))
                                    <a href="{{ route('admin.ecommerce-categorias.show', $categoria->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                    @if(\App\Helpers\AdminHelper::hasPermission('ecommerce-categorias.edit'))
                                    <a href="{{ route('admin.ecommerce-categorias.edit', $categoria->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    @if(\App\Helpers\AdminHelper::hasPermission('ecommerce-categorias.delete'))
                                    <button 
                                        type="button" 
                                        onclick="confirmarEliminacion({{ $categoria->id }}, '{{ $categoria->nombre }}')" 
                                        class="text-red-600 hover:text-red-900" 
                                        title="Eliminar"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron categorías.
                                @if(\App\Helpers\AdminHelper::hasPermission('ecommerce-categorias.create'))
                                    <a href="{{ route('admin.ecommerce-categorias.create') }}" class="text-blue-600 hover:text-blue-900 ml-2">
                                        Crear una nueva categoría
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($categorias->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $categorias->links() }}
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
                    ¿Está seguro de que desea eliminar la categoría <strong id="categoriaNombre"></strong>? Esta acción no se puede deshacer.
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
        document.getElementById('categoriaNombre').textContent = nombre;
        document.getElementById('formEliminar').action = '{{ route("admin.ecommerce-categorias.destroy", ":id") }}'.replace(':id', id);
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
