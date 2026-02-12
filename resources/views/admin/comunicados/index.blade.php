@extends('admin.layouts.app')

@section('title', 'Comunicados - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Comunicados</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de comunicados y anuncios</p>
        </div>
        <div>
            <a href="{{ route('admin.comunicados.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Comunicado
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.comunicados.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Búsqueda -->
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                placeholder="Título, contenido..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Tipo -->
        <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select name="tipo" id="tipo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="general" {{ request('tipo') == 'general' ? 'selected' : '' }}>General</option>
                <option value="urgente" {{ request('tipo') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                <option value="informativo" {{ request('tipo') == 'informativo' ? 'selected' : '' }}>Informativo</option>
                <option value="mantenimiento" {{ request('tipo') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
            </select>
        </div>

        <!-- Publicado -->
        <div>
            <label for="publicado" class="block text-sm font-medium text-gray-700 mb-1">Estado de Publicación</label>
            <select name="publicado" id="publicado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="1" {{ request('publicado') == '1' ? 'selected' : '' }}>Publicado</option>
                <option value="0" {{ request('publicado') == '0' ? 'selected' : '' }}>No Publicado</option>
            </select>
        </div>

        <!-- Visibilidad -->
        <div>
            <label for="visible_para" class="block text-sm font-medium text-gray-700 mb-1">Visibilidad</label>
            <select name="visible_para" id="visible_para" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="todos" {{ request('visible_para') == 'todos' ? 'selected' : '' }}>Todos</option>
                <option value="propietarios" {{ request('visible_para') == 'propietarios' ? 'selected' : '' }}>Propietarios</option>
                <option value="arrendatarios" {{ request('visible_para') == 'arrendatarios' ? 'selected' : '' }}>Arrendatarios</option>
                <option value="administracion" {{ request('visible_para') == 'administracion' ? 'selected' : '' }}>Administración</option>
            </select>
        </div>

        <!-- Fecha Desde -->
        <div>
            <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Fecha Hasta -->
        <div>
            <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.comunicados.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Comunicados -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="table-container">
        <table class="table-domoph min-w-full">
            <thead>
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Título
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha Publicación
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Visibilidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Autor
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($comunicados as $comunicado)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ Str::limit($comunicado->titulo, 50) }}
                            </div>
                            @if($comunicado->resumen)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ Str::limit($comunicado->resumen, 60) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($comunicado->tipo == 'urgente') bg-red-100 text-red-800
                                @elseif($comunicado->tipo == 'informativo') bg-blue-100 text-blue-800
                                @elseif($comunicado->tipo == 'mantenimiento') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($comunicado->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($comunicado->fecha_publicacion)
                                {{ $comunicado->fecha_publicacion->format('d/m/Y H:i') }}
                            @else
                                <span class="text-gray-400">No publicado</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($comunicado->visible_para) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($comunicado->publicado)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Publicado
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Borrador
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $comunicado->autor->nombre ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.comunicados.edit', $comunicado->id) }}" 
                                class="text-blue-600 hover:text-blue-900" 
                                title="Editar comunicado">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontraron comunicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $comunicados->links() }}
    </div>
</div>
@endsection
