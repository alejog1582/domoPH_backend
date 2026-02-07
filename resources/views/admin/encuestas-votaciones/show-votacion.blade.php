@extends('admin.layouts.app')

@section('title', 'Resultados de Votación - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Resultados de Votación</h1>
            <p class="mt-2 text-sm text-gray-600">Información y resultados de la votación</p>
        </div>
        <div class="flex items-center gap-2">
            @if(\App\Helpers\AdminHelper::hasPermission('votaciones.edit'))
            <a href="{{ route('admin.encuestas-votaciones.edit', ['id' => $votacion->id, 'tipo' => 'votacion']) }}" 
               class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            @endif
            <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'votaciones']) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
</div>

@if($propiedad)
    <!-- Información de la Votación -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información General</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <p class="text-sm text-gray-900">{{ $votacion->titulo }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                @php
                    $estadoColors = [
                        'activa' => 'bg-green-100 text-green-800',
                        'cerrada' => 'bg-gray-100 text-gray-800',
                        'anulada' => 'bg-red-100 text-red-800',
                    ];
                    $color = $estadoColors[$votacion->estado] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                    {{ ucfirst($votacion->estado) }}
                </span>
            </div>
            
            @if($votacion->descripcion)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <p class="text-sm text-gray-900">{{ $votacion->descripcion }}</p>
            </div>
            @endif
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
                <p class="text-sm text-gray-900">{{ $votacion->fecha_inicio->format('d/m/Y') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin</label>
                <p class="text-sm text-gray-900">{{ $votacion->fecha_fin->format('d/m/Y') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total de Votos</label>
                <p class="text-sm text-gray-900 font-semibold">{{ $votacion->votos->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Resultados por Opción -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Resultados por Opción</h2>
        
        @if($votacion->opciones->count() > 0)
            <div class="space-y-4">
                @php
                    $totalVotos = $votacion->votos->count();
                @endphp
                @foreach($votacion->opciones as $opcion)
                    @php
                        $votosOpcion = $votacion->votos->where('opcion_id', $opcion->id)->count();
                        $porcentaje = $totalVotos > 0 ? ($votosOpcion / $totalVotos) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900">{{ $opcion->texto_opcion }}</span>
                                <span class="text-xs text-gray-500">({{ $votosOpcion }} votos)</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($porcentaje, 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No hay opciones definidas para esta votación.</p>
        @endif
    </div>

    <!-- Detalle de Votos -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Detalle de Votos ({{ $votacion->votos->count() }})</h2>
        </div>

        @if($votacion->votos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opción Votada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($votacion->votos as $voto)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $voto->residente->user->nombre ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $voto->residente->user->email ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $voto->residente->unidad->numero ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $voto->opcion->texto_opcion }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $voto->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">No hay votos registrados para esta votación.</p>
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
