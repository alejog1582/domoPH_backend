@extends('admin.layouts.app')

@section('title', 'Publicaciones Ecommerce - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Publicaciones Ecommerce</h1>
            <p class="mt-2 text-sm text-gray-600">Administración de publicaciones del ecommerce interno</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.create'))
        <a href="{{ route('admin.ecommerce.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-plus mr-2"></i>
            Crear Publicación
        </a>
        @endif
    </div>
</div>

@if($propiedad)
    <!-- Sección de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.ecommerce.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Búsqueda general -->
                <div>
                    <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        id="buscar" 
                        name="buscar" 
                        value="{{ request('buscar') }}" 
                        placeholder="Título o descripción"
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
                        <option value="en_revision" {{ request('estado') == 'en_revision' ? 'selected' : '' }}>En Revisión</option>
                        <option value="publicado" {{ request('estado') == 'publicado' ? 'selected' : '' }}>Publicado</option>
                        <option value="pausado" {{ request('estado') == 'pausado' ? 'selected' : '' }}>Pausado</option>
                        <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                    </select>
                </div>

                <!-- Filtro Tipo -->
                <div>
                    <label for="tipo_publicacion" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select 
                        id="tipo_publicacion" 
                        name="tipo_publicacion" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="venta" {{ request('tipo_publicacion') == 'venta' ? 'selected' : '' }}>Venta</option>
                        <option value="arriendo" {{ request('tipo_publicacion') == 'arriendo' ? 'selected' : '' }}>Arriendo</option>
                        <option value="servicio" {{ request('tipo_publicacion') == 'servicio' ? 'selected' : '' }}>Servicio</option>
                        <option value="otro" {{ request('tipo_publicacion') == 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <!-- Filtro Categoría -->
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                    <select 
                        id="categoria_id" 
                        name="categoria_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todas</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.ecommerce.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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

    <!-- Tabla de Publicaciones -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">TÍTULO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CATEGORÍA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">TIPO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">RESIDENTE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">PRECIO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">FECHA PUBLICACIÓN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($publicaciones as $publicacion)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ Str::limit($publicacion->titulo, 40) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $publicacion->categoria_nombre }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $tipoLabels = [
                                        'venta' => 'Venta',
                                        'arriendo' => 'Arriendo',
                                        'servicio' => 'Servicio',
                                        'otro' => 'Otro'
                                    ];
                                    $tipoLabel = $tipoLabels[$publicacion->tipo_publicacion] ?? ucfirst($publicacion->tipo_publicacion);
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $tipoLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $publicacion->residente_nombre ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($publicacion->precio)
                                    ${{ number_format($publicacion->precio, 0, ',', '.') }} {{ $publicacion->moneda }}
                                    @if($publicacion->es_negociable)
                                        <span class="text-xs text-gray-500">(Negociable)</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                @php
                    $estadoColors = [
                        'en_revision' => 'bg-orange-100 text-orange-800',
                        'publicado' => 'bg-green-100 text-green-800',
                        'pausado' => 'bg-yellow-100 text-yellow-800',
                        'finalizado' => 'bg-gray-100 text-gray-800',
                    ];
                    $color = $estadoColors[$publicacion->estado] ?? 'bg-gray-100 text-gray-800';
                    $estadoLabels = [
                        'en_revision' => 'En Revisión',
                        'publicado' => 'Publicado',
                        'pausado' => 'Pausado',
                        'finalizado' => 'Finalizado',
                    ];
                    $estadoLabel = $estadoLabels[$publicacion->estado] ?? ucfirst($publicacion->estado);
                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                    {{ $estadoLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($publicacion->fecha_publicacion)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.view'))
                                    <a href="{{ route('admin.ecommerce.show', $publicacion->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                    @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.edit'))
                                    <a href="{{ route('admin.ecommerce.edit', $publicacion->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.delete'))
                                    <button 
                                        type="button" 
                                        onclick="confirmarEliminacion({{ $publicacion->id }}, '{{ addslashes($publicacion->titulo) }}')" 
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
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron publicaciones.
                                @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.create'))
                                    <a href="{{ route('admin.ecommerce.create') }}" class="text-blue-600 hover:text-blue-900 ml-2">
                                        Crear una nueva publicación
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($publicaciones->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $publicaciones->links() }}
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
                    ¿Está seguro de que desea eliminar la publicación <strong id="publicacionTitulo"></strong>? Esta acción no se puede deshacer.
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
    function confirmarEliminacion(id, titulo) {
        document.getElementById('publicacionTitulo').textContent = titulo;
        document.getElementById('formEliminar').action = '{{ route("admin.ecommerce.destroy", ":id") }}'.replace(':id', id);
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
