@extends('admin.layouts.app')

@section('title', 'Comunicaciones del Consejo')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Comunicaciones del Consejo</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de comunicaciones del Consejo de Administración</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('consejo-comunicaciones.create'))
        <a href="{{ route('admin.consejo-comunicaciones.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Comunicación
        </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
@endif

<!-- Filtros -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" action="{{ route('admin.consejo-comunicaciones.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="estado" id="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="publicada" {{ request('estado') == 'publicada' ? 'selected' : '' }}>Publicada</option>
                </select>
            </div>
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select name="tipo" id="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="informativa" {{ request('tipo') == 'informativa' ? 'selected' : '' }}>Informativa</option>
                    <option value="urgente" {{ request('tipo') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                    <option value="circular" {{ request('tipo') == 'circular' ? 'selected' : '' }}>Circular</option>
                    <option value="recordatorio" {{ request('tipo') == 'recordatorio' ? 'selected' : '' }}>Recordatorio</option>
                </select>
            </div>
            <div>
                <label for="visible_para" class="block text-sm font-medium text-gray-700 mb-2">Visible Para</label>
                <select name="visible_para" id="visible_para" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="consejo" {{ request('visible_para') == 'consejo' ? 'selected' : '' }}>Consejo</option>
                    <option value="propietarios" {{ request('visible_para') == 'propietarios' ? 'selected' : '' }}>Propietarios</option>
                    <option value="residentes" {{ request('visible_para') == 'residentes' ? 'selected' : '' }}>Residentes</option>
                    <option value="todos" {{ request('visible_para') == 'todos' ? 'selected' : '' }}>Todos</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Lista de Comunicaciones -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    @if($comunicaciones->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visible Para</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Publicación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($comunicaciones as $comunicacion)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $comunicacion->titulo }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ Str::limit(strip_tags($comunicacion->contenido), 80) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $tipoColors = [
                                'informativa' => 'bg-blue-100 text-blue-800',
                                'urgente' => 'bg-red-100 text-red-800',
                                'circular' => 'bg-purple-100 text-purple-800',
                                'recordatorio' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $tipoColor = $tipoColors[$comunicacion->tipo] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $tipoColor }}">
                            {{ ucfirst($comunicacion->tipo) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ ucfirst($comunicacion->visible_para) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($comunicacion->estado == 'borrador')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                Borrador
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Publicada
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($comunicacion->fecha_publicacion)
                            {{ \Carbon\Carbon::parse($comunicacion->fecha_publicacion)->format('d/m/Y H:i') }}
                        @else
                            <span class="text-gray-400">No publicada</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.consejo-comunicaciones.show', $comunicacion->id) }}" 
                               class="text-blue-600 hover:text-blue-900" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if($comunicacion->estado == 'borrador')
                                @if(\App\Helpers\AdminHelper::hasPermission('consejo-comunicaciones.edit'))
                                <a href="{{ route('admin.consejo-comunicaciones.edit', $comunicacion->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                @if(\App\Helpers\AdminHelper::hasPermission('consejo-comunicaciones.delete'))
                                <form action="{{ route('admin.consejo-comunicaciones.destroy', $comunicacion->id) }}" 
                                      method="POST" class="inline" 
                                      onsubmit="return confirm('¿Está seguro de eliminar esta comunicación?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif

                                @if(\App\Helpers\AdminHelper::hasPermission('consejo-comunicaciones.publicar'))
                                <form action="{{ route('admin.consejo-comunicaciones.publicar', $comunicacion->id) }}" 
                                      method="POST" class="inline" 
                                      onsubmit="return confirm('¿Está seguro de publicar esta comunicación? Una vez publicada no se podrá editar ni eliminar.');">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="text-green-600 hover:text-green-900" title="Publicar">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $comunicaciones->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
        <p class="text-gray-500 text-lg">No hay comunicaciones registradas</p>
        @if(\App\Helpers\AdminHelper::hasPermission('consejo-comunicaciones.create'))
        <a href="{{ route('admin.consejo-comunicaciones.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Primera Comunicación
        </a>
        @endif
    </div>
    @endif
</div>
@endsection
