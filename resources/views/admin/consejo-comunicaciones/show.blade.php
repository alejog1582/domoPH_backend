@extends('admin.layouts.app')

@section('title', 'Detalle de Comunicación')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-comunicaciones.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Comunicaciones
    </a>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Comunicación</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $comunicacion->titulo }}</p>
        </div>
        <div class="flex items-center space-x-3">
            @if($puede_editar && \App\Helpers\AdminHelper::hasPermission('consejo-comunicaciones.edit'))
            <a href="{{ route('admin.consejo-comunicaciones.edit', $comunicacion->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            @endif
            @if($puede_eliminar && \App\Helpers\AdminHelper::hasPermission('consejo-comunicaciones.delete'))
            <form action="{{ route('admin.consejo-comunicaciones.destroy', $comunicacion->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta comunicación?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>
                    Eliminar
                </button>
            </form>
            @endif
            @if($comunicacion->estado == 'borrador' && \App\Helpers\AdminHelper::hasPermission('consejo-comunicaciones.publicar'))
            <form action="{{ route('admin.consejo-comunicaciones.publicar', $comunicacion->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de publicar esta comunicación? Una vez publicada no se podrá editar ni eliminar.');">
                @csrf
                @method('POST')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Publicar
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
@endif

@if($comunicacion->estado == 'publicada')
<div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <p class="text-sm text-blue-800">
        <i class="fas fa-info-circle mr-2"></i>
        Esta comunicación está publicada. No se puede editar ni eliminar.
    </p>
</div>
@endif

<!-- Información de la Comunicación -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Comunicación</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">Título</label>
            <p class="text-lg font-semibold text-gray-900">{{ $comunicacion->titulo }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Tipo</label>
            @php
                $tipoColors = [
                    'informativa' => 'bg-blue-100 text-blue-800',
                    'urgente' => 'bg-red-100 text-red-800',
                    'circular' => 'bg-purple-100 text-purple-800',
                    'recordatorio' => 'bg-yellow-100 text-yellow-800',
                ];
                $tipoColor = $tipoColors[$comunicacion->tipo] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $tipoColor }}">
                {{ ucfirst($comunicacion->tipo) }}
            </span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Visible Para</label>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                {{ ucfirst($comunicacion->visible_para) }}
            </span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
            @if($comunicacion->estado == 'borrador')
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Borrador</span>
            @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Publicada</span>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Publicación</label>
            @if($comunicacion->fecha_publicacion)
                <p class="text-gray-900">{{ \Carbon\Carbon::parse($comunicacion->fecha_publicacion)->format('d/m/Y H:i') }}</p>
            @else
                <p class="text-gray-500">No publicada</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Creada</label>
            <p class="text-gray-900">{{ \Carbon\Carbon::parse($comunicacion->created_at)->format('d/m/Y H:i') }}</p>
        </div>

        @if($comunicacion->updated_at != $comunicacion->created_at)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Última Actualización</label>
            <p class="text-gray-900">{{ \Carbon\Carbon::parse($comunicacion->updated_at)->format('d/m/Y H:i') }}</p>
        </div>
        @endif

        @if(in_array($comunicacion->visible_para, ['residentes', 'propietarios', 'todos']) && $comunicacion->estado == 'publicada')
        <div class="md:col-span-2">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm text-green-800">
                    <i class="fas fa-check-circle mr-2"></i>
                    Esta comunicación está visible en el frontend para {{ $comunicacion->visible_para }}.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Contenido -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Contenido</h2>
    <div class="prose max-w-none">
        {!! $comunicacion->contenido !!}
    </div>
</div>

<!-- Archivos Adjuntos -->
@if($archivos->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Archivos Adjuntos</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamaño</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($archivos as $archivo)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $archivo->nombre_archivo }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $archivo->tipo_archivo ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $archivo->tamaño ? number_format($archivo->tamaño / 1024, 2) . ' KB' : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($archivo->created_at)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ $archivo->ruta_archivo }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Ver
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-gray-50 rounded-lg shadow p-6 text-center">
    <p class="text-gray-500">No hay archivos adjuntos</p>
</div>
@endif
@endsection
