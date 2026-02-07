@extends('admin.layouts.app')

@section('title', 'Detalles de Encuesta - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalles de Encuesta</h1>
            <p class="mt-2 text-sm text-gray-600">Información y respuestas de la encuesta</p>
        </div>
        <div class="flex items-center gap-2">
            @if(\App\Helpers\AdminHelper::hasPermission('encuestas.edit'))
            <a href="{{ route('admin.encuestas-votaciones.edit', ['id' => $encuesta->id, 'tipo' => 'encuesta']) }}" 
               class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            @endif
            <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'encuestas']) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
</div>

@if($propiedad)
    <!-- Información de la Encuesta -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información General</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <p class="text-sm text-gray-900">{{ $encuesta->titulo }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                @php
                    $estadoColors = [
                        'activa' => 'bg-green-100 text-green-800',
                        'cerrada' => 'bg-gray-100 text-gray-800',
                        'anulada' => 'bg-red-100 text-red-800',
                    ];
                    $color = $estadoColors[$encuesta->estado] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                    {{ ucfirst($encuesta->estado) }}
                </span>
            </div>
            
            @if($encuesta->descripcion)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <p class="text-sm text-gray-900">{{ $encuesta->descripcion }}</p>
            </div>
            @endif
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Respuesta</label>
                <p class="text-sm text-gray-900">{{ $encuesta->tipo_respuesta == 'respuesta_abierta' ? 'Respuesta Abierta' : 'Opción Múltiple' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
                <p class="text-sm text-gray-900">{{ $encuesta->fecha_inicio->format('d/m/Y') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin</label>
                <p class="text-sm text-gray-900">{{ $encuesta->fecha_fin->format('d/m/Y') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total de Respuestas</label>
                <p class="text-sm text-gray-900 font-semibold">{{ $encuesta->respuestas->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Respuestas -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Respuestas ({{ $encuesta->respuestas->count() }})</h2>
        </div>

        @if($encuesta->respuestas->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Respuesta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($encuesta->respuestas as $respuesta)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $respuesta->residente->user->nombre ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $respuesta->residente->user->email ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $respuesta->residente->unidad->numero ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($respuesta->respuesta_abierta)
                                    <div class="max-w-md">
                                        <p class="text-sm">{{ $respuesta->respuesta_abierta }}</p>
                                    </div>
                                @elseif($respuesta->opcion)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $respuesta->opcion->texto_opcion }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Sin respuesta</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $respuesta->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">No hay respuestas registradas para esta encuesta.</p>
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
@endsection
